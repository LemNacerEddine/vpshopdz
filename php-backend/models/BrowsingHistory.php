<?php
/**
 * Browsing History Model
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/helpers.php';

class BrowsingHistory {
    private $conn;
    private $table = 'browsing_history';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get browsing history with full product details
    public function getHistory($userId = null, $browserId = null, $limit = 20) {
        $where = [];
        $params = [];

        if ($userId) {
            $where[] = 'bh.user_id = :user_id';
            $params[':user_id'] = $userId;
        } elseif ($browserId) {
            $where[] = 'bh.browser_id = :browser_id';
            $params[':browser_id'] = $browserId;
        } else {
            return [];
        }

        // Get unique products from history
        $query = "SELECT DISTINCT p.*, MAX(bh.viewed_at) as last_viewed
                  FROM {$this->table} bh
                  JOIN products p ON bh.product_id = p.product_id
                  WHERE " . implode(' AND ', $where) . "
                  GROUP BY p.product_id
                  ORDER BY last_viewed DESC
                  LIMIT " . (int)$limit;

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
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
                'viewed_at' => $row['last_viewed']
            ];
        }

        return $products;
    }

    // Add to browsing history
    public function addToHistory($productId, $userId = null, $browserId = null) {
        if (!$userId && !$browserId) return false;

        // Check if product exists
        $checkQuery = "SELECT product_id FROM products WHERE product_id = :product_id";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->execute([':product_id' => $productId]);
        if (!$checkStmt->fetch()) {
            return false;
        }

        $query = "INSERT INTO {$this->table} (user_id, browser_id, product_id, viewed_at)
                  VALUES (:user_id, :browser_id, :product_id, NOW())";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':user_id' => $userId,
            ':browser_id' => $browserId,
            ':product_id' => $productId
        ]);
    }

    // Clear browsing history
    public function clearHistory($userId = null, $browserId = null) {
        $where = [];
        $params = [];

        if ($userId) {
            $where[] = 'user_id = :user_id';
            $params[':user_id'] = $userId;
        }
        
        if ($browserId) {
            $where[] = 'browser_id = :browser_id';
            $params[':browser_id'] = $browserId;
        }
        
        if (empty($where)) {
            return false;
        }

        // Use OR condition like Python version
        $query = "DELETE FROM {$this->table} WHERE " . implode(' OR ', $where);
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }
}
