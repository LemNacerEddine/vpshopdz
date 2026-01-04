<?php
/**
 * Order Controller
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class OrderController {
    private $db;
    private $order;
    private $auth;

    public function __construct($db) {
        $this->db = $db;
        $this->order = new Order($db);
        $this->auth = new Auth();
    }

    // Get all orders (admin)
    public function index() {
        $this->auth->requireAdmin();
        
        $filters = [
            'status' => $_GET['status'] ?? null,
            'limit' => $_GET['limit'] ?? 50
        ];

        $orders = $this->order->getAll($filters);
        jsonResponse($orders);
    }

    // Get user's orders
    public function myOrders() {
        $user = $this->auth->requireAuth();
        
        $orders = $this->order->getAll(['user_id' => $user['user_id']]);
        jsonResponse($orders);
    }

    // Get single order
    public function show($orderId) {
        $user = $this->auth->getCurrentUser();
        $order = $this->order->findById($orderId);
        
        if (!$order) {
            errorResponse('الطلب غير موجود', 404);
        }

        // Check access
        if ($user && $user['role'] !== 'admin' && $order['user_id'] !== $user['user_id']) {
            errorResponse('غير مصرح بالوصول', 403);
        }

        jsonResponse($order);
    }

    // Create order
    public function store() {
        $data = getJsonInput();
        $user = $this->auth->getCurrentUser();

        // Validation
        if (empty($data['customer_name']) || empty($data['customer_phone']) || empty($data['shipping_address'])) {
            errorResponse('الاسم ورقم الهاتف والعنوان مطلوبون', 400);
        }

        if (empty($data['items']) || !is_array($data['items'])) {
            errorResponse('يجب إضافة منتجات للطلب', 400);
        }

        // Add user_id if authenticated
        if ($user) {
            $data['user_id'] = $user['user_id'];
        }

        $order = $this->order->create($data);
        jsonResponse($order, 201);
    }

    // Update order status (admin only)
    public function updateStatus($orderId) {
        $this->auth->requireAdmin();
        $data = getJsonInput();

        if (empty($data['status'])) {
            errorResponse('الحالة مطلوبة', 400);
        }

        $existing = $this->order->findById($orderId);
        if (!$existing) {
            errorResponse('الطلب غير موجود', 404);
        }

        $order = $this->order->updateStatus($orderId, $data['status']);
        
        if (!$order) {
            errorResponse('حالة غير صالحة', 400);
        }

        jsonResponse($order);
    }
}
