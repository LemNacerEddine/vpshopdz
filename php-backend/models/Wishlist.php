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

    // Get user's wishlist with full product details
    public function getByUser($userId) {
        $query = "SELECT p.*, w.created_at as added_at
                  FROM {$this->table} w
                  JOIN products p ON w.product_id = p.product_id
                  WHERE w.user_id = :user_id
                  ORDER BY w.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll();

        $products = [];
        foreach ($rows as $row) {
            // Get product images
            $imgQuery = "SELECT image_url FROM product_images WHERE product_id = :product_id ORDER BY sort_order";
            $imgStmt = $this->conn->prepare($imgQuery);
            $imgStmt->execute([':product_id' => $row['product_id']]);
            $images = $imgStmt->fetchAll(PDO::FETCH_COLUMN);

            // Calculate final price with discount
            $price = (float)$row['price'];
            $finalPrice = $price;
            if (!empty($row['discount_percent']) && $row['discount_percent'] > 0) {
                $now = new DateTime();
                $start = !empty($row['discount_start']) ? new DateTime($row['discount_start']) : null;
                $end = !empty($row['discount_end']) ? new DateTime($row['discount_end']) : null;
                
                if ((!$start || $now >= $start) && (!$end || $now <= $end)) {
                    $finalPrice = $price * (1 - $row['discount_percent'] / 100);
                }
            }

            $products[] = [
                'product_id' => $row['product_id'],
                'name_ar' => $row['name_ar'],
                'name_fr' => $row['name_fr'] ?? null,
                'name_en' => $row['name_en'] ?? null,
                'description_ar' => $row['description_ar'] ?? null,
                'description_fr' => $row['description_fr'] ?? null,
                'description_en' => $row['description_en'] ?? null,
                'price' => $finalPrice,
                'original_price' => $price,
                'old_price' => !empty($row['old_price']) ? (float)$row['old_price'] : null,
                'stock' => (int)($row['stock'] ?? 0),
                'category_id' => $row['category_id'] ?? null,
                'images' => $images,
                'featured' => (bool)($row['featured'] ?? false),
                'unit' => $row['unit'] ?? 'piece',
                'discount_percent' => !empty($row['discount_percent']) ? (int)$row['discount_percent'] : null,
                'discount_start' => $row['discount_start'] ?? null,
                'discount_end' => $row['discount_end'] ?? null,
                'rating' => !empty($row['rating']) ? (float)$row['rating'] : 0,
                'reviews_count' => (int)($row['reviews_count'] ?? 0),
                'created_at' => $row['created_at'] ?? null,
                'added_at' => $row['added_at']
            ];
        }

        return $products;
    }

    // Add to wishlist
    public function add($userId, $productId) {
        // Check if already exists
        if ($this->exists($userId, $productId)) {
            return true;
        }

        // Check if product exists
        $checkQuery = "SELECT product_id FROM products WHERE product_id = :product_id";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->execute([':product_id' => $productId]);
        if (!$checkStmt->fetch()) {
            return false;
        }

        $query = "INSERT INTO {$this->table} (user_id, product_id, created_at) VALUES (:user_id, :product_id, NOW())";
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
