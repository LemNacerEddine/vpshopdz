<?php
/**
 * Wishlist Model
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/helpers.php';

class Wishlist {
    private $conn;
    private $table = 'wishlists';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get user's wishlist
    public function getByUser($userId) {
        $query = "SELECT p.*, GROUP_CONCAT(pi.image_url ORDER BY pi.sort_order) as images_str
                  FROM {$this->table} w
                  JOIN products p ON w.product_id = p.product_id
                  LEFT JOIN product_images pi ON p.product_id = pi.product_id
                  WHERE w.user_id = :user_id
                  GROUP BY p.product_id
                  ORDER BY w.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $userId]);
        $products = $stmt->fetchAll();

        foreach ($products as &$product) {
            $product['images'] = $product['images_str'] ? explode(',', $product['images_str']) : [];
            unset($product['images_str']);
            unset($product['id']);
        }

        return $products;
    }

    // Add to wishlist
    public function add($userId, $productId) {
        // Check if already exists
        if ($this->exists($userId, $productId)) {
            return true;
        }

        $query = "INSERT INTO {$this->table} (user_id, product_id) VALUES (:user_id, :product_id)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':user_id' => $userId, ':product_id' => $productId]);
    }

    // Remove from wishlist
    public function remove($userId, $productId) {
        $query = "DELETE FROM {$this->table} WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':user_id' => $userId, ':product_id' => $productId]);
    }

    // Check if exists
    public function exists($userId, $productId) {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $userId, ':product_id' => $productId]);
        return $stmt->fetch()['count'] > 0;
    }
}
