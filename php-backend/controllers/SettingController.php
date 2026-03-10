<?php
/**
 * Setting Controller
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class SettingController {
    private $db;
    private $model;
    private $auth;

    public function __construct($db) {
        $this->db = $db;
        $this->model = new Setting($db);
        $this->auth = new Auth();
    }

    /**
     * GET /admin/settings
     * Get all settings (admin only)
     */
    public function index() {
        $this->auth->requireAdmin();

        $settings = $this->model->getAll();
        jsonResponse($settings);
    }

    /**
     * GET /settings/public
     * Public endpoint - returns only non-sensitive settings (pixel ID, etc.)
     */
    public function getPublic() {
        $pixelId = $this->model->get('fb_pixel_id', '');
        $storeSettings = $this->model->getByPrefix('store_');
        jsonResponse(array_merge($storeSettings, [
            'fb_pixel_id' => $pixelId,
        ]));
    }

    /**
     * GET /admin/settings/store
     * Get store business info settings
     */
    public function getStoreSettings() {
        $this->auth->requireAdmin();
        $settings = $this->model->getByPrefix('store_');
        jsonResponse($settings);
    }

    /**
     * GET /admin/settings/whatsapp
     * Get WhatsApp-related settings only
     */
    public function getWhatsApp() {
        $this->auth->requireAdmin();

        $settings = $this->model->getByPrefix('whatsapp_');
        $settings['store_url'] = $this->model->get('store_url', '');
        $settings['fb_pixel_id'] = $this->model->get('fb_pixel_id', '');
        $settings['green_api_instance_id'] = $this->model->get('green_api_instance_id', '');
        $settings['green_api_token'] = $this->model->get('green_api_token', '');
        $settings['fb_app_id'] = $this->model->get('fb_app_id', '');
        $settings['fb_app_secret'] = $this->model->get('fb_app_secret', '');
        $settings['fb_access_token'] = $this->model->get('fb_access_token', '');
        $settings['fb_ad_account_id'] = $this->model->get('fb_ad_account_id', '');
        $settings['fb_page_id'] = $this->model->get('fb_page_id', '');
        // Offer settings
        $settings['offer_discount_enabled'] = $this->model->get('offer_discount_enabled', 'false');
        $settings['offer_discount_type'] = $this->model->get('offer_discount_type', 'percentage');
        $settings['offer_discount_value'] = $this->model->get('offer_discount_value', '10');
        $settings['offer_free_shipping'] = $this->model->get('offer_free_shipping', 'false');
        jsonResponse($settings);
    }

    /**
     * PUT /admin/settings
     * Update settings (admin only)
     */
    public function update() {
        $this->auth->requireAdmin();
        $data = getJsonInput();

        if (empty($data) || !is_array($data)) {
            errorResponse('لا توجد بيانات', 400);
        }

        // Whitelist of allowed setting keys
        $allowed = [
            'whatsapp_enabled', 'whatsapp_phone_number_id', 'whatsapp_access_token',
            'whatsapp_auto_send', 'whatsapp_delay_minutes',
            'whatsapp_message_ar', 'whatsapp_message_fr', 'whatsapp_message_en',
            'store_url', 'fb_pixel_id',
            'whatsapp_mode', 'green_api_instance_id', 'green_api_token',
            'whatsapp_rate_limit_seconds',
            'whatsapp_max_retries', 'whatsapp_max_per_run',
            'whatsapp_phone_cooldown_minutes',
            'whatsapp_send_window_start', 'whatsapp_send_window_end',
            'offer_discount_enabled', 'offer_discount_type', 'offer_discount_value', 'offer_free_shipping',
            'fb_app_id', 'fb_app_secret', 'fb_access_token', 'fb_ad_account_id', 'fb_page_id',
            // Store business info
            'store_name', 'store_email', 'store_phone', 'store_address',
            'store_currency', 'store_language', 'store_description',
            'store_logo', 'store_rc', 'store_nif', 'store_nis', 'store_ai',
            'store_facebook', 'store_instagram', 'store_website',
        ];

        $filtered = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $filtered[$key] = $value;
            }
        }

        $this->model->setMany($filtered);
        jsonResponse(['success' => true]);
    }

    /**
     * POST /admin/settings/whatsapp/test
     * Send a test WhatsApp message (supports both modes)
     */
    public function testWhatsApp() {
        $this->auth->requireAdmin();
        $data = getJsonInput();

        $phone = $data['phone'] ?? null;
        if (!$phone) {
            errorResponse('رقم الهاتف مطلوب', 400);
        }

        // Format phone number
        $phone = preg_replace('/\s+|-/', '', $phone);
        if (strpos($phone, '0') === 0) {
            $phone = '213' . substr($phone, 1);
        } elseif (strpos($phone, '+') === 0) {
            $phone = substr($phone, 1);
        }

        $message = $data['message'] ?? 'Test message from AgroYousfi';
        $mode = $this->model->get('whatsapp_mode', 'green_api');

        if ($mode === 'business_api') {
            $phoneNumberId = $this->model->get('whatsapp_phone_number_id');
            $accessToken = $this->model->get('whatsapp_access_token');

            if (!$phoneNumberId || !$accessToken) {
                errorResponse('إعدادات واتساب Business API غير مكتملة', 400);
            }

            $result = self::sendWhatsAppMessage($phoneNumberId, $accessToken, $phone, $message);
        } else {
            $instanceId = $this->model->get('green_api_instance_id');
            $token = $this->model->get('green_api_token');

            if (!$instanceId || !$token) {
                errorResponse('إعدادات Green API غير مكتملة', 400);
            }

            $result = self::sendGreenApiMessage($instanceId, $token, $phone, $message);
        }

        if ($result['success']) {
            jsonResponse(['success' => true, 'message_id' => $result['message_id'] ?? null]);
        } else {
            errorResponse($result['error'] ?? 'فشل إرسال الرسالة', 400);
        }
    }

    /**
     * Send WhatsApp message via Meta Cloud API
     */
    public static function sendWhatsAppMessage($phoneNumberId, $accessToken, $to, $message) {
        $url = "https://graph.facebook.com/v21.0/{$phoneNumberId}/messages";

        $payload = json_encode([
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $message],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => 'Connection error: ' . $error];
        }

        $data = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300 && isset($data['messages'][0]['id'])) {
            return ['success' => true, 'message_id' => $data['messages'][0]['id']];
        }

        $errorMsg = $data['error']['message'] ?? 'Unknown API error (HTTP ' . $httpCode . ')';
        return ['success' => false, 'error' => $errorMsg];
    }

    /**
     * Send WhatsApp message via Green API (personal WhatsApp)
     */
    public static function sendGreenApiMessage($instanceId, $token, $to, $message) {
        // Green API expects chatId format: 213XXXXXXXXX@c.us
        $chatId = preg_replace('/[^0-9]/', '', $to);
        if (strpos($chatId, '0') === 0) {
            $chatId = '213' . substr($chatId, 1);
        }
        $chatId .= '@c.us';

        $url = "https://api.green-api.com/waInstance{$instanceId}/sendMessage/{$token}";

        $payload = json_encode([
            'chatId' => $chatId,
            'message' => $message,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => 'Connection error: ' . $error];
        }

        $data = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300 && isset($data['idMessage'])) {
            return ['success' => true, 'message_id' => $data['idMessage']];
        }

        $errorMsg = $data['message'] ?? ($data['error'] ?? 'Unknown Green API error (HTTP ' . $httpCode . ')');
        return ['success' => false, 'error' => $errorMsg];
    }
}
