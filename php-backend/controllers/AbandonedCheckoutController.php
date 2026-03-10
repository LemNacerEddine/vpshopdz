<?php
/**
 * Abandoned Checkout Controller
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../models/AbandonedCheckout.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../controllers/SettingController.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class AbandonedCheckoutController {
    private $db;
    private $model;
    private $auth;

    public function __construct($db) {
        $this->db = $db;
        $this->model = new AbandonedCheckout($db);
        $this->auth = new Auth();
    }

    /**
     * POST /abandoned-checkouts
     * Save/update abandoned checkout (called from frontend auto-save)
     */
    public function save() {
        $data = getJsonInput();
        $user = $this->auth->getCurrentUser();

        if ($user) {
            $data['user_id'] = $user['user_id'];
        }

        if (empty($data['items']) || !is_array($data['items']) || count($data['items']) === 0) {
            errorResponse('لا توجد منتجات', 400);
        }

        $result = $this->model->save($data);
        jsonResponse($result);
    }

    /**
     * DELETE /abandoned-checkouts
     * Remove abandoned checkout when order is completed
     */
    public function resolve() {
        $data = getJsonInput();
        $user = $this->auth->getCurrentUser();

        $userId = $user ? $user['user_id'] : null;
        $browserId = $data['browser_id'] ?? null;
        $orderId = $data['order_id'] ?? null;
        $checkoutId = $data['checkout_id'] ?? null;
        $actualTotal = isset($data['actual_total']) ? floatval($data['actual_total']) : null;

        // If checkout_id is provided (from recovery link), mark as recovered (counts in stats)
        if ($checkoutId) {
            $this->model->markRecovered($checkoutId, $orderId, $actualTotal);
        } else {
            // Normal order (not from recovery link) - just delete the abandoned checkout
            // Don't mark as "recovered" to keep recovery stats accurate
            $this->model->deleteByIdentifier($userId, $browserId);
        }
        jsonResponse(['success' => true]);
    }

    /**
     * GET /admin/abandoned-checkouts
     * List all abandoned checkouts (admin only)
     */
    public function index() {
        $this->auth->requireAdmin();

        $filters = [
            'has_phone' => $_GET['has_phone'] ?? null,
            'notified' => $_GET['notified'] ?? null,
            'send_status' => $_GET['send_status'] ?? null,
            'recovered' => $_GET['recovered'] ?? null,
            'search' => $_GET['search'] ?? null,
            'limit' => $_GET['limit'] ?? 50,
        ];

        $checkouts = $this->model->getAll($filters);
        jsonResponse($checkouts);
    }

    /**
     * GET /admin/abandoned-checkouts/stats
     * Get stats for dashboard
     */
    public function stats() {
        $this->auth->requireAdmin();

        $stats = $this->model->getStats();
        jsonResponse($stats);
    }

    /**
     * GET /admin/abandoned-checkouts/:id
     * Get single abandoned checkout details
     */
    public function show($checkoutId) {
        $this->auth->requireAdmin();

        $checkout = $this->model->findById($checkoutId);
        if (!$checkout) {
            errorResponse('غير موجود', 404);
        }
        jsonResponse($checkout);
    }

    /**
     * PUT /admin/abandoned-checkouts/:id/notified
     * Mark as notified (WhatsApp sent)
     */
    public function markNotified($checkoutId) {
        $this->auth->requireAdmin();

        $checkout = $this->model->findById($checkoutId);
        if (!$checkout) {
            errorResponse('غير موجود', 404);
        }

        $result = $this->model->markNotified($checkoutId);
        jsonResponse($result);
    }

    /**
     * PUT /admin/abandoned-checkouts/:id/recovered
     * Mark as recovered manually
     */
    public function markRecovered($checkoutId) {
        $this->auth->requireAdmin();

        $checkout = $this->model->findById($checkoutId);
        if (!$checkout) {
            errorResponse('غير موجود', 404);
        }

        $result = $this->model->markRecovered($checkoutId);
        jsonResponse($result);
    }

    /**
     * DELETE /admin/abandoned-checkouts/:id
     * Delete a single abandoned checkout
     */
    public function destroy($checkoutId) {
        $this->auth->requireAdmin();

        $checkout = $this->model->findById($checkoutId);
        if (!$checkout) {
            errorResponse('غير موجود', 404);
        }

        $query = "DELETE FROM abandoned_checkouts WHERE checkout_id = :checkout_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':checkout_id' => $checkoutId]);

        jsonResponse(['success' => true]);
    }

    /**
     * PUT /admin/abandoned-checkouts/:id/retry
     * Reset send status to retry sending
     */
    public function retrySend($checkoutId) {
        $this->auth->requireAdmin();

        $checkout = $this->model->findById($checkoutId);
        if (!$checkout) {
            errorResponse('غير موجود', 404);
        }

        $result = $this->model->resetSendStatus($checkoutId);
        jsonResponse($result);
    }

    /**
     * PUT /admin/abandoned-checkouts/:id/skip
     * Skip sending for this checkout
     */
    public function skipSend($checkoutId) {
        $this->auth->requireAdmin();

        $checkout = $this->model->findById($checkoutId);
        if (!$checkout) {
            errorResponse('غير موجود', 404);
        }

        $result = $this->model->markSkipped($checkoutId);
        jsonResponse($result);
    }

    /**
     * GET /recover/:checkout_id
     * Public endpoint - returns checkout data + offer settings for recovery link
     */
    public function recover($checkoutId) {
        $checkout = $this->model->findById($checkoutId);
        if (!$checkout || $checkout['recovered']) {
            errorResponse('غير موجود أو تم استرداده', 404);
        }

        // Get offer settings
        $settings = new Setting($this->db);
        $offer = [
            'discount_enabled' => $settings->get('offer_discount_enabled', 'false') === 'true',
            'discount_type' => $settings->get('offer_discount_type', 'percentage'),
            'discount_value' => $settings->get('offer_discount_value', '10'),
            'free_shipping' => $settings->get('offer_free_shipping', 'false') === 'true',
        ];

        // Parse items JSON if needed
        $items = $checkout['items'];
        if (is_string($items)) {
            $items = json_decode($items, true);
        }

        jsonResponse([
            'checkout_id' => $checkout['checkout_id'],
            'customer_name' => $checkout['customer_name'],
            'customer_phone' => $checkout['customer_phone'],
            'shipping_address' => $checkout['shipping_address'],
            'wilaya' => $checkout['wilaya'],
            'commune' => $checkout['commune'],
            'items' => $items,
            'cart_total' => $checkout['cart_total'],
            'item_count' => $checkout['item_count'],
            'offer' => $offer,
        ]);
    }
}
