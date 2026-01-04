<?php
/**
 * Order Model
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/helpers.php';

class Order {
    private $conn;
    private $table = 'orders';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all orders with filters
    public function getAll($filters = []) {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'o.status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['user_id'])) {
            $where[] = 'o.user_id = :user_id';
            $params[':user_id'] = $filters['user_id'];
        }

        $limit = isset($filters['limit']) ? (int)$filters['limit'] : 50;

        $query = "SELECT o.* FROM {$this->table} o
                  WHERE " . implode(' AND ', $where) . "
                  ORDER BY o.created_at DESC
                  LIMIT {$limit}";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        // Get items for each order
        foreach ($orders as &$order) {
            $order['items'] = $this->getOrderItems($order['order_id']);
            unset($order['id']);
        }

        return $orders;
    }

    // Get single order
    public function findById($orderId) {
        $query = "SELECT * FROM {$this->table} WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':order_id' => $orderId]);
        $order = $stmt->fetch();

        if ($order) {
            $order['items'] = $this->getOrderItems($orderId);
            unset($order['id']);
        }

        return $order;
    }

    // Get order items
    private function getOrderItems($orderId) {
        $query = "SELECT * FROM order_items WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':order_id' => $orderId]);
        $items = $stmt->fetchAll();
        
        foreach ($items as &$item) {
            unset($item['id']);
        }
        
        return $items;
    }

    // Create order
    public function create($data) {
        $orderId = generateId('order_');

        $query = "INSERT INTO {$this->table} 
                  (order_id, user_id, customer_name, customer_phone, customer_email, 
                   shipping_address, wilaya, total, payment_method, notes)
                  VALUES 
                  (:order_id, :user_id, :customer_name, :customer_phone, :customer_email,
                   :shipping_address, :wilaya, :total, :payment_method, :notes)";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':order_id' => $orderId,
            ':user_id' => $data['user_id'] ?? null,
            ':customer_name' => $data['customer_name'],
            ':customer_phone' => $data['customer_phone'],
            ':customer_email' => $data['customer_email'] ?? null,
            ':shipping_address' => $data['shipping_address'],
            ':wilaya' => $data['wilaya'] ?? null,
            ':total' => $data['total'],
            ':payment_method' => $data['payment_method'] ?? 'cod',
            ':notes' => $data['notes'] ?? null
        ]);

        // Add order items
        if (!empty($data['items'])) {
            $this->addOrderItems($orderId, $data['items']);
        }

        return $this->findById($orderId);
    }

    // Add order items
    private function addOrderItems($orderId, $items) {
        $query = "INSERT INTO order_items (order_id, product_id, product_name, product_image, quantity, price)
                  VALUES (:order_id, :product_id, :product_name, :product_image, :quantity, :price)";
        $stmt = $this->conn->prepare($query);

        foreach ($items as $item) {
            $stmt->execute([
                ':order_id' => $orderId,
                ':product_id' => $item['product_id'],
                ':product_name' => $item['product_name'] ?? '',
                ':product_image' => $item['product_image'] ?? null,
                ':quantity' => $item['quantity'],
                ':price' => $item['price']
            ]);
        }
    }

    // Update order status
    public function updateStatus($orderId, $status) {
        $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        $query = "UPDATE {$this->table} SET status = :status WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':status' => $status, ':order_id' => $orderId]);

        return $this->findById($orderId);
    }

    // Count orders
    public function count($status = null) {
        $query = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];
        
        if ($status) {
            $query .= " WHERE status = :status";
            $params[':status'] = $status;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch()['count'];
    }

    // Get total revenue
    public function getTotalRevenue() {
        $query = "SELECT COALESCE(SUM(total), 0) as revenue FROM {$this->table} WHERE status NOT IN ('cancelled')";
        $stmt = $this->conn->query($query);
        return $stmt->fetch()['revenue'];
    }

    // Get orders by status counts
    public function getStatusCounts() {
        $query = "SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status";
        $stmt = $this->conn->query($query);
        $results = $stmt->fetchAll();
        
        $counts = [];
        foreach ($results as $row) {
            $counts[$row['status']] = (int)$row['count'];
        }
        
        return $counts;
    }

    // Get recent orders
    public function getRecent($limit = 5, $status = null) {
        $query = "SELECT * FROM {$this->table}";
        $params = [];
        
        if ($status) {
            $query .= " WHERE status = :status";
            $params[':status'] = $status;
        }
        
        $query .= " ORDER BY created_at DESC LIMIT " . (int)$limit;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        foreach ($orders as &$order) {
            $order['items'] = $this->getOrderItems($order['order_id']);
            unset($order['id']);
        }

        return $orders;
    }
}
