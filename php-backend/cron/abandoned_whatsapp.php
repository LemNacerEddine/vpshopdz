<?php
/**
 * Cron Job: Shopify-like WhatsApp Auto-Send for Abandoned Checkouts
 * AgroYousfi E-commerce
 *
 * Features:
 *   - Atomic row locking (prevents duplicate sends)
 *   - MySQL GET_LOCK (prevents concurrent cron execution)
 *   - Exponential backoff retries (5min, 10min, 20min... capped at 6h)
 *   - Send window (configurable business hours)
 *   - Phone cooldown (don't message same phone twice in X hours)
 *   - Configurable max messages per run
 *   - Stale lock cleanup (crash recovery)
 *   - Detailed file logging
 *
 * Supports two WhatsApp modes:
 *   - green_api: Free personal WhatsApp via Green API
 *   - business_api: Meta WhatsApp Business Cloud API (Pro)
 *
 * cPanel Scheduled Task (every 2 minutes):
 *   /usr/bin/php82 /home/USER/public_html/api/cron/abandoned_whatsapp.php
 */

// Prevent web access
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('CLI only');
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../models/AbandonedCheckout.php';
require_once __DIR__ . '/../controllers/SettingController.php';
require_once __DIR__ . '/../utils/helpers.php';

// ─── Logging ───
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/abandoned_whatsapp_' . date('Y-m-d') . '.log';

function logMsg($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $line = "[{$timestamp}] {$message}\n";
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    echo $line;
}

$startTime = microtime(true);
logMsg("=== Starting abandoned checkout WhatsApp cron ===");

// ─── Database Connection ───
try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    logMsg("FATAL: Database connection failed: " . $e->getMessage());
    exit(1);
}

// ─── Distributed Lock (prevent concurrent execution) ───
$lockName = 'agroyousfi_abandoned_whatsapp';
$lockStmt = $db->prepare("SELECT GET_LOCK(:lock_name, 0) as acquired");
$lockStmt->execute([':lock_name' => $lockName]);
$lockResult = $lockStmt->fetch(PDO::FETCH_ASSOC);

if (!$lockResult || $lockResult['acquired'] != 1) {
    logMsg("Another instance is already running. Exiting.");
    exit(0);
}

// Release lock on exit
register_shutdown_function(function() use ($db, $lockName) {
    try {
        $db->prepare("SELECT RELEASE_LOCK(:lock_name)")->execute([':lock_name' => $lockName]);
    } catch (Exception $e) {
        // Ignore
    }
});

$settings = new Setting($db);
$abandonedModel = new AbandonedCheckout($db);

// ─── Check if enabled ───
$whatsappEnabled = $settings->get('whatsapp_enabled', 'false') === 'true';
$autoSendEnabled = $settings->get('whatsapp_auto_send', 'false') === 'true';

if (!$whatsappEnabled || !$autoSendEnabled) {
    logMsg("Auto-send disabled (whatsapp_enabled={$whatsappEnabled}, whatsapp_auto_send={$autoSendEnabled}). Exiting.");
    exit(0);
}

// ─── Load settings ───
$mode = $settings->get('whatsapp_mode', 'green_api');
$delayMinutes = max(1, (int)$settings->get('whatsapp_delay_minutes', '30'));
$rateLimitSeconds = max(10, (int)$settings->get('whatsapp_rate_limit_seconds', '120'));
$maxRetries = max(1, (int)$settings->get('whatsapp_max_retries', '5'));
$maxPerRun = max(1, (int)$settings->get('whatsapp_max_per_run', '10'));
$cooldownMinutes = max(0, (int)$settings->get('whatsapp_phone_cooldown_minutes', '1440'));
$sendWindowStart = (int)$settings->get('whatsapp_send_window_start', '9');
$sendWindowEnd = (int)$settings->get('whatsapp_send_window_end', '21');

logMsg("Config: mode={$mode}, delay={$delayMinutes}min, rate_limit={$rateLimitSeconds}s, max_retries={$maxRetries}, max_per_run={$maxPerRun}, cooldown={$cooldownMinutes}min, window={$sendWindowStart}h-{$sendWindowEnd}h");

// ─── Send Window Check ───
$currentHour = (int)date('G');
if ($sendWindowStart < $sendWindowEnd) {
    // Normal window e.g. 9-21
    if ($currentHour < $sendWindowStart || $currentHour >= $sendWindowEnd) {
        logMsg("Outside send window ({$sendWindowStart}h-{$sendWindowEnd}h, current={$currentHour}h). Exiting.");
        exit(0);
    }
} else {
    // Overnight window e.g. 21-9
    if ($currentHour >= $sendWindowEnd && $currentHour < $sendWindowStart) {
        logMsg("Outside send window ({$sendWindowStart}h-{$sendWindowEnd}h, current={$currentHour}h). Exiting.");
        exit(0);
    }
}

// ─── Validate credentials ───
if ($mode === 'business_api') {
    $phoneNumberId = $settings->get('whatsapp_phone_number_id', '');
    $accessToken = $settings->get('whatsapp_access_token', '');
    if (empty($phoneNumberId) || empty($accessToken)) {
        logMsg("ERROR: WhatsApp Business API credentials not configured.");
        exit(1);
    }
} else {
    $greenInstanceId = $settings->get('green_api_instance_id', '');
    $greenToken = $settings->get('green_api_token', '');
    if (empty($greenInstanceId) || empty($greenToken)) {
        logMsg("ERROR: Green API credentials not configured.");
        exit(1);
    }
}

// ─── Clean stale locks (crash recovery) ───
$staleCleaned = $abandonedModel->cleanStaleLocks(10);
if ($staleCleaned > 0) {
    logMsg("Cleaned {$staleCleaned} stale processing locks.");
}

// ─── Get message template ───
$messageTemplate = $settings->get('whatsapp_message_ar', 'مرحباً {name}! لم تكمل طلبك. أكمل الآن: {link}');
$storeUrl = $settings->get('store_url', '');

// ─── Debug: detailed per-record diagnosis ───
$debugStmt = $db->query("SELECT
    checkout_id,
    customer_name,
    customer_phone,
    send_status,
    send_attempts,
    item_count,
    recovered,
    processing_at,
    notified_at,
    next_retry_at,
    updated_at,
    NOW() as server_now,
    TIMESTAMPDIFF(MINUTE, updated_at, NOW()) as minutes_since_update,
    CASE WHEN customer_phone IS NOT NULL AND customer_phone != '' THEN 'YES' ELSE 'NO' END as has_phone,
    CASE WHEN send_status IS NULL OR send_status IN ('pending', 'failed') THEN 'YES' ELSE 'NO' END as status_ok,
    CASE WHEN updated_at < DATE_SUB(NOW(), INTERVAL {$delayMinutes} MINUTE) THEN 'YES' ELSE 'NO' END as old_enough,
    CASE WHEN processing_at IS NULL THEN 'YES' ELSE 'NO' END as not_locked
    FROM abandoned_checkouts WHERE recovered = FALSE AND item_count > 0");
$allRows = $debugStmt->fetchAll(PDO::FETCH_ASSOC);
logMsg("DB has " . count($allRows) . " non-recovered checkouts with items:");
foreach ($allRows as $row) {
    $phoneInCooldown = 'NO';
    if (!empty($row['customer_phone'])) {
        $cdStmt = $db->prepare("SELECT COUNT(*) as cnt FROM abandoned_checkouts WHERE customer_phone = :phone AND notified_at > DATE_SUB(NOW(), INTERVAL :cooldown MINUTE)");
        $cdStmt->execute([':phone' => $row['customer_phone'], ':cooldown' => $cooldownMinutes]);
        $cd = $cdStmt->fetch(PDO::FETCH_ASSOC);
        if ($cd['cnt'] > 0) $phoneInCooldown = 'YES';
    }
    logMsg("  [{$row['checkout_id']}] phone={$row['customer_phone']}, status={$row['send_status']}, attempts={$row['send_attempts']}, has_phone={$row['has_phone']}, status_ok={$row['status_ok']}, old_enough={$row['old_enough']}(updated {$row['minutes_since_update']}min ago), not_locked={$row['not_locked']}, phone_in_cooldown={$phoneInCooldown}, notified_at={$row['notified_at']}, next_retry_at={$row['next_retry_at']}");
}

// ─── Get eligible checkouts ───
$checkouts = $abandonedModel->getEligibleForSend($delayMinutes, $maxRetries, $maxPerRun, $cooldownMinutes);
$count = count($checkouts);

logMsg("Found {$count} eligible checkouts.");

if ($count === 0) {
    logMsg("Nothing to send. Exiting.");
    exit(0);
}

$sent = 0;
$failed = 0;
$skipped = 0;

foreach ($checkouts as $index => $checkout) {
    $checkoutId = $checkout['checkout_id'];

    // ─── Atomic row claim ───
    if (!$abandonedModel->lockForProcessing($checkoutId)) {
        logMsg("  [{$checkoutId}] Could not acquire row lock (already being processed). Skipping.");
        $skipped++;
        continue;
    }

    // ─── Format phone number (Algeria) ───
    $phone = preg_replace('/\s+|-/', '', $checkout['customer_phone']);
    if (strpos($phone, '0') === 0) {
        $phone = '213' . substr($phone, 1);
    } elseif (strpos($phone, '+') === 0) {
        $phone = substr($phone, 1);
    } elseif (!preg_match('/^213/', $phone)) {
        $phone = '213' . $phone;
    }

    // ─── Build personalized message ───
    $name = $checkout['customer_name'] ?: 'عميلنا الكريم';
    $checkoutLink = $storeUrl ? rtrim($storeUrl, '/') . '/recover/' . $checkoutId : '{link}';
    $cartTotalValue = (float)($checkout['cart_total'] ?? 0);
    $total = number_format($cartTotalValue, 0, '.', ',') . ' د.ج';
    $itemsCount = (string)($checkout['item_count'] ?? 0);

    // ─── Calculate discounted total (apply recovery offer) ───
    $discountEnabled = $settings->get('offer_discount_enabled', 'false') === 'true';
    $discountType = $settings->get('offer_discount_type', 'percentage');
    $discountValue = (float)$settings->get('offer_discount_value', '10');
    $freeShippingOffer = $settings->get('offer_free_shipping', 'false') === 'true';

    $discountAmount = 0;
    if ($discountEnabled && $discountValue > 0) {
        if ($discountType === 'percentage') {
            $discountAmount = round($cartTotalValue * $discountValue / 100);
        } else {
            $discountAmount = min($discountValue, $cartTotalValue);
        }
    }
    $newTotalValue = max(0, $cartTotalValue - $discountAmount);
    $newTotal = number_format($newTotalValue, 0, '.', ',') . ' د.ج';

    // Build discount description
    $discountText = '';
    if ($discountEnabled && $discountValue > 0) {
        if ($discountType === 'percentage') {
            $discountText = $discountValue . '%';
        } else {
            $discountText = number_format($discountValue, 0, '.', ',') . ' د.ج';
        }
    }
    if ($freeShippingOffer) {
        $discountText .= ($discountText ? ' + ' : '') . 'شحن مجاني';
    }

    $message = str_replace(
        ['{name}', '{link}', '{checkout_id}', '{total}', '{new_total}', '{discount}', '{items_count}'],
        [$name, $checkoutLink, $checkoutId, $total, $newTotal, $discountText, $itemsCount],
        $messageTemplate
    );

    $attempt = ($checkout['send_attempts'] ?? 0) + 1;
    logMsg("  [{$checkoutId}] Sending to {$phone} ({$name}), attempt #{$attempt}...");

    // ─── Send message ───
    if ($mode === 'business_api') {
        $result = SettingController::sendWhatsAppMessage($phoneNumberId, $accessToken, $phone, $message);
    } else {
        $result = SettingController::sendGreenApiMessage($greenInstanceId, $greenToken, $phone, $message);
    }

    if ($result['success']) {
        $abandonedModel->markSent($checkoutId, $result['message_id'] ?? null);
        $sent++;
        logMsg("  [{$checkoutId}] OK - message_id: " . ($result['message_id'] ?? 'N/A'));
    } else {
        $error = $result['error'] ?? 'Unknown error';
        $abandonedModel->markFailed($checkoutId, $error, $maxRetries);
        $failed++;
        logMsg("  [{$checkoutId}] FAILED - {$error}");

        // If ban/block detected on Green API, stop immediately
        if ($mode === 'green_api' && (
            stripos($error, 'ban') !== false ||
            stripos($error, 'block') !== false ||
            stripos($error, 'unauthorized') !== false
        )) {
            logMsg("  WARNING: Possible account restriction detected. Stopping immediately.");
            break;
        }
    }

    // ─── Rate limiting between messages ───
    if ($index < $count - 1) {
        logMsg("  Waiting {$rateLimitSeconds}s (rate limit)...");
        sleep($rateLimitSeconds);
    }
}

$duration = round(microtime(true) - $startTime, 2);
logMsg("=== Completed: {$sent} sent, {$failed} failed, {$skipped} skipped. Duration: {$duration}s ===");
