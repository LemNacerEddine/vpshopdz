<?php
/**
 * Abandoned Checkout Model
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/helpers.php';

class AbandonedCheckout {
    private $conn;
    private $table = 'abandoned_checkouts';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Save or update an abandoned checkout
     * Uses browser_id or user_id as unique identifier
     */
    public function save($data) {
        $userId = $data['user_id'] ?? null;
        $browserId = $data['browser_id'] ?? null;

        // Try to find existing abandoned checkout for this user/browser
        $existing = $this->findByIdentifier($userId, $browserId);

        if ($existing) {
            return $this->update($existing['checkout_id'], $data);
        }

        return $this->create($data);
    }

    public function create($data) {
        $checkoutId = generateId('ac_');

        $query = "INSERT INTO {$this->table}
                  (checkout_id, user_id, browser_id, customer_name, customer_phone,
                   shipping_address, wilaya, commune, items, cart_total, item_count)
                  VALUES
                  (:checkout_id, :user_id, :browser_id, :customer_name, :customer_phone,
                   :shipping_address, :wilaya, :commune, :items, :cart_total, :item_count)";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':checkout_id' => $checkoutId,
            ':user_id' => $data['user_id'] ?? null,
            ':browser_id' => $data['browser_id'] ?? null,
            ':customer_name' => $data['customer_name'] ?? null,
            ':customer_phone' => $data['customer_phone'] ?? null,
            ':shipping_address' => $data['shipping_address'] ?? null,
            ':wilaya' => $data['wilaya'] ?? null,
            ':commune' => $data['commune'] ?? null,
            ':items' => json_encode($data['items'] ?? [], JSON_UNESCAPED_UNICODE),
            ':cart_total' => $data['cart_total'] ?? 0,
            ':item_count' => $data['item_count'] ?? 0,
        ]);

        return $this->findById($checkoutId);
    }

    public function update($checkoutId, $data) {
        // Fetch current data to compare - avoid unnecessary updates that reset updated_at
        $current = $this->findById($checkoutId);
        if (!$current) {
            return null;
        }

        $fields = [];
        $params = [':checkout_id' => $checkoutId];

        $allowedFields = [
            'customer_name', 'customer_phone', 'shipping_address',
            'wilaya', 'commune', 'cart_total', 'item_count'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $newVal = (string)$data[$field];
                $curVal = (string)($current[$field] ?? '');
                if ($newVal !== $curVal) {
                    $fields[] = "{$field} = :{$field}";
                    $params[":{$field}"] = $data[$field];
                }
            }
        }

        if (isset($data['items'])) {
            $newItems = json_encode($data['items'], JSON_UNESCAPED_UNICODE);
            $curItems = json_encode($current['items'] ?? [], JSON_UNESCAPED_UNICODE);
            if ($newItems !== $curItems) {
                $fields[] = "items = :items";
                $params[':items'] = $newItems;
            }
        }

        if (empty($fields)) {
            return $current;
        }

        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) .
                 " WHERE checkout_id = :checkout_id AND recovered = FALSE";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return $this->findById($checkoutId);
    }

    public function findById($checkoutId) {
        $query = "SELECT * FROM {$this->table} WHERE checkout_id = :checkout_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':checkout_id' => $checkoutId]);
        $row = $stmt->fetch();

        if ($row) {
            $row['items'] = json_decode($row['items'], true) ?: [];
            unset($row['id']);
        }

        return $row;
    }

    public function findByIdentifier($userId = null, $browserId = null) {
        if ($userId) {
            $query = "SELECT * FROM {$this->table} WHERE user_id = :user_id AND recovered = FALSE ORDER BY updated_at DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':user_id' => $userId]);
        } elseif ($browserId) {
            $query = "SELECT * FROM {$this->table} WHERE browser_id = :browser_id AND recovered = FALSE ORDER BY updated_at DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':browser_id' => $browserId]);
        } else {
            return null;
        }

        $row = $stmt->fetch();
        if ($row) {
            $row['items'] = json_decode($row['items'], true) ?: [];
            unset($row['id']);
        }

        return $row;
    }

    /**
     * Get all abandoned checkouts for admin (not recovered, with items)
     */
    public function getAll($filters = []) {
        $where = ['item_count > 0'];
        $params = [];

        // Filter by recovered status
        if (!empty($filters['recovered']) && $filters['recovered'] === 'true') {
            $where[] = "recovered = TRUE";
        } elseif (isset($filters['recovered']) && $filters['recovered'] === 'all') {
            // Show all (no filter)
        } else {
            $where[] = "recovered = FALSE";
        }

        if (!empty($filters['has_phone'])) {
            $where[] = "customer_phone IS NOT NULL AND customer_phone != ''";
        }

        if (!empty($filters['notified'])) {
            if ($filters['notified'] === 'yes') {
                $where[] = "notified_at IS NOT NULL";
            } else {
                $where[] = "notified_at IS NULL";
            }
        }

        if (!empty($filters['send_status'])) {
            if ($filters['send_status'] === 'pending') {
                $where[] = "(send_status = 'pending' OR send_status IS NULL)";
            } else {
                $where[] = "send_status = :send_status";
                $params[':send_status'] = $filters['send_status'];
            }
        }

        if (!empty($filters['search'])) {
            $where[] = "(customer_name LIKE :search OR customer_phone LIKE :search2)";
            $params[':search'] = '%' . $filters['search'] . '%';
            $params[':search2'] = '%' . $filters['search'] . '%';
        }

        $limit = isset($filters['limit']) ? (int)$filters['limit'] : 50;

        $query = "SELECT * FROM {$this->table}
                  WHERE " . implode(' AND ', $where) . "
                  ORDER BY updated_at DESC
                  LIMIT {$limit}";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['items'] = json_decode($row['items'], true) ?: [];
            unset($row['id']);
        }

        return $rows;
    }

    /**
     * Mark as recovered (when customer eventually places order)
     */
    public function markRecovered($checkoutId, $orderId = null, $actualTotal = null) {
        $setClauses = 'recovered = TRUE, recovered_order_id = :order_id';
        $params = [
            ':checkout_id' => $checkoutId,
            ':order_id' => $orderId
        ];

        if ($actualTotal !== null) {
            $setClauses .= ', cart_total = :actual_total';
            $params[':actual_total'] = $actualTotal;
        }

        $query = "UPDATE {$this->table} SET {$setClauses} WHERE checkout_id = :checkout_id";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return $this->findById($checkoutId);
    }

    /**
     * Mark by user/browser when they complete an order
     */
    public function markRecoveredByIdentifier($userId = null, $browserId = null, $orderId = null, $actualTotal = null) {
        $where = 'recovered = FALSE';
        $params = [':order_id' => $orderId];
        $setClauses = 'recovered = TRUE, recovered_order_id = :order_id';

        if ($actualTotal !== null) {
            $setClauses .= ', cart_total = :actual_total';
            $params[':actual_total'] = $actualTotal;
        }

        if ($userId) {
            $where .= ' AND user_id = :user_id';
            $params[':user_id'] = $userId;
        } elseif ($browserId) {
            $where .= ' AND browser_id = :browser_id';
            $params[':browser_id'] = $browserId;
        } else {
            return false;
        }

        $query = "UPDATE {$this->table}
                  SET {$setClauses}
                  WHERE {$where}";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * Delete abandoned checkout by user/browser identifier
     * Used for normal orders (not recovery) to avoid inflating recovery stats
     */
    public function deleteByIdentifier($userId = null, $browserId = null) {
        $where = 'recovered = FALSE';
        $params = [];

        if ($userId) {
            $where .= ' AND user_id = :user_id';
            $params[':user_id'] = $userId;
        } elseif ($browserId) {
            $where .= ' AND browser_id = :browser_id';
            $params[':browser_id'] = $browserId;
        } else {
            return false;
        }

        $query = "DELETE FROM {$this->table} WHERE {$where}";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * Mark as notified (WhatsApp message sent)
     */
    public function markNotified($checkoutId) {
        $query = "UPDATE {$this->table} SET notified_at = NOW() WHERE checkout_id = :checkout_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':checkout_id' => $checkoutId]);
        return $this->findById($checkoutId);
    }

    /**
     * Delete old abandoned checkouts (cleanup)
     */
    public function deleteOld($days = 30) {
        $query = "DELETE FROM {$this->table} WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':days' => $days]);
        return $stmt->rowCount();
    }

    /**
     * Get stats for dashboard
     */
    public function getStats() {
        $query = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN customer_phone IS NOT NULL AND customer_phone != '' THEN 1 ELSE 0 END) as with_phone,
                    SUM(CASE WHEN notified_at IS NOT NULL THEN 1 ELSE 0 END) as notified,
                    SUM(CASE WHEN send_status = 'sent' THEN 1 ELSE 0 END) as auto_sent,
                    SUM(CASE WHEN send_status = 'failed' THEN 1 ELSE 0 END) as auto_failed,
                    SUM(CASE WHEN send_status = 'pending' OR send_status IS NULL THEN 1 ELSE 0 END) as auto_pending,
                    COALESCE(SUM(cart_total), 0) as total_value
                  FROM {$this->table}
                  WHERE recovered = FALSE AND item_count > 0";

        $stmt = $this->conn->query($query);
        $stats = $stmt->fetch();

        // Recovery rate
        $queryRecovered = "SELECT
                    COUNT(CASE WHEN recovered = TRUE THEN 1 END) as recovered,
                    COUNT(*) as total
                  FROM {$this->table}
                  WHERE item_count > 0";
        $stmt2 = $this->conn->query($queryRecovered);
        $recovery = $stmt2->fetch();

        // Cast all numeric values to int to prevent JS string concatenation bugs
        $stats['total'] = (int)($stats['total'] ?? 0);
        $stats['with_phone'] = (int)($stats['with_phone'] ?? 0);
        $stats['notified'] = (int)($stats['notified'] ?? 0);
        $stats['auto_sent'] = (int)($stats['auto_sent'] ?? 0);
        $stats['auto_failed'] = (int)($stats['auto_failed'] ?? 0);
        $stats['auto_pending'] = (int)($stats['auto_pending'] ?? 0);
        $stats['total_value'] = (float)($stats['total_value'] ?? 0);

        $stats['recovered'] = (int)($recovery['recovered'] ?? 0);
        $stats['recovery_rate'] = $recovery['total'] > 0
            ? round(($recovery['recovered'] / $recovery['total']) * 100, 1)
            : 0;

        return $stats;
    }

    /**
     * Get checkouts eligible for auto-send (used by cron)
     */
    public function getEligibleForSend($delayMinutes, $maxRetries, $maxPerRun, $cooldownMinutes) {
        $query = "SELECT * FROM {$this->table}
                  WHERE recovered = FALSE
                    AND item_count > 0
                    AND customer_phone IS NOT NULL
                    AND customer_phone != ''
                    AND (send_status IS NULL OR send_status IN ('pending', 'failed'))
                    AND (send_status IS NULL OR send_status = 'pending' OR (send_status = 'failed' AND send_attempts < :max_retries))
                    AND (next_retry_at IS NULL OR next_retry_at <= NOW())
                    AND updated_at < DATE_SUB(NOW(), INTERVAL :delay MINUTE)
                    AND processing_at IS NULL
                    AND customer_phone NOT IN (
                        SELECT customer_phone FROM {$this->table}
                        WHERE notified_at > DATE_SUB(NOW(), INTERVAL :cooldown MINUTE)
                          AND customer_phone IS NOT NULL
                          AND customer_phone != ''
                    )
                  ORDER BY
                    CASE WHEN send_status = 'pending' THEN 0 ELSE 1 END,
                    updated_at ASC
                  LIMIT :max_per_run";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':max_retries', (int)$maxRetries, PDO::PARAM_INT);
        $stmt->bindValue(':delay', (int)$delayMinutes, PDO::PARAM_INT);
        $stmt->bindValue(':cooldown', (int)$cooldownMinutes, PDO::PARAM_INT);
        $stmt->bindValue(':max_per_run', (int)$maxPerRun, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lock a row for processing (atomic claim)
     */
    public function lockForProcessing($checkoutId) {
        $query = "UPDATE {$this->table}
                  SET processing_at = NOW()
                  WHERE checkout_id = :checkout_id
                    AND processing_at IS NULL
                    AND (send_status IS NULL OR send_status IN ('pending', 'failed'))";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':checkout_id' => $checkoutId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Mark as sent successfully
     */
    public function markSent($checkoutId, $messageId = null) {
        $query = "UPDATE {$this->table}
                  SET send_status = 'sent',
                      notified_at = NOW(),
                      last_attempt_at = NOW(),
                      processing_at = NULL,
                      send_attempts = send_attempts + 1,
                      whatsapp_message_id = :message_id,
                      last_error = NULL,
                      next_retry_at = NULL
                  WHERE checkout_id = :checkout_id";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':checkout_id' => $checkoutId,
            ':message_id' => $messageId,
        ]);
    }

    /**
     * Mark as failed with error and schedule retry
     */
    public function markFailed($checkoutId, $error, $maxRetries = 5) {
        // Calculate exponential backoff: min(2^attempts * 5, 360) minutes
        $query = "UPDATE {$this->table}
                  SET send_status = CASE
                        WHEN send_attempts + 1 >= :max_retries THEN 'failed'
                        ELSE 'failed'
                      END,
                      last_attempt_at = NOW(),
                      processing_at = NULL,
                      send_attempts = send_attempts + 1,
                      last_error = :error,
                      next_retry_at = CASE
                        WHEN send_attempts + 1 >= :max_retries2 THEN NULL
                        ELSE DATE_ADD(NOW(), INTERVAL LEAST(POW(2, send_attempts) * 5, 360) MINUTE)
                      END
                  WHERE checkout_id = :checkout_id";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':checkout_id' => $checkoutId,
            ':error' => $error,
            ':max_retries' => (int)$maxRetries,
            ':max_retries2' => (int)$maxRetries,
        ]);
    }

    /**
     * Unlock stale processing rows (cron crash recovery)
     */
    public function cleanStaleLocks($minutesThreshold = 10) {
        $query = "UPDATE {$this->table}
                  SET processing_at = NULL
                  WHERE processing_at IS NOT NULL
                    AND processing_at < DATE_SUB(NOW(), INTERVAL :minutes MINUTE)";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':minutes' => $minutesThreshold]);
        return $stmt->rowCount();
    }

    /**
     * Reset send status for retry (admin action)
     */
    public function resetSendStatus($checkoutId) {
        $query = "UPDATE {$this->table}
                  SET send_status = 'pending',
                      send_attempts = 0,
                      last_error = NULL,
                      next_retry_at = NULL,
                      processing_at = NULL,
                      notified_at = NULL,
                      whatsapp_message_id = NULL
                  WHERE checkout_id = :checkout_id";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':checkout_id' => $checkoutId]);
        return $this->findById($checkoutId);
    }

    /**
     * Skip a checkout (don't send)
     */
    public function markSkipped($checkoutId) {
        $query = "UPDATE {$this->table}
                  SET send_status = 'skipped',
                      processing_at = NULL,
                      next_retry_at = NULL
                  WHERE checkout_id = :checkout_id";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':checkout_id' => $checkoutId]);
        return $this->findById($checkoutId);
    }
}
