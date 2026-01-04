<?php
/**
 * Admin Controller
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class AdminController {
    private $db;
    private $auth;

    public function __construct($db) {
        $this->db = $db;
        $this->auth = new Auth();
    }

    // Get dashboard stats
    public function dashboard() {
        $this->auth->requireAdmin();

        $userModel = new User($this->db);
        $productModel = new Product($this->db);
        $categoryModel = new Category($this->db);
        $orderModel = new Order($this->db);

        $stats = [
            'total_users' => $userModel->count(),
            'total_products' => $productModel->count(),
            'total_categories' => $categoryModel->count(),
            'total_orders' => $orderModel->count(),
            'pending_orders' => $orderModel->count('pending'),
            'total_revenue' => (float)$orderModel->getTotalRevenue(),
            'order_status_counts' => $orderModel->getStatusCounts(),
            'recent_orders' => $orderModel->getRecent(5)
        ];

        jsonResponse($stats);
    }

    // Get all users
    public function users() {
        $this->auth->requireAdmin();

        $userModel = new User($this->db);
        $limit = $_GET['limit'] ?? 100;
        $offset = $_GET['offset'] ?? 0;

        $users = $userModel->getAll($limit, $offset);
        jsonResponse($users);
    }

    // Get unprocessed orders
    public function unprocessedOrders() {
        $this->auth->requireAdmin();

        $orderModel = new Order($this->db);
        $orders = $orderModel->getRecent(10, 'pending');
        jsonResponse($orders);
    }
}
