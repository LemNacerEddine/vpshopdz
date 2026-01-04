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

    // Get browsing history
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

        $query = "SELECT DISTINCT p.*, 
                         GROUP_CONCAT(DISTINCT pi.image_url ORDER BY pi.sort_order) as images_str,
                         MAX(bh.viewed_at) as last_viewed
                  FROM {$this->table} bh
                  JOIN products p ON bh.product_id = p.product_id
                  LEFT JOIN product_images pi ON p.product_id = pi.product_id
                  WHERE " . implode(' AND ', $where) . "
                  GROUP BY p.product_id
                  ORDER BY last_viewed DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();

        foreach ($products as &$product) {
            $product['images'] = $product['images_str'] ? explode(',', $product['images_str']) : [];
            unset($product['images_str']);
            unset($product['id']);
        }

        return $products;
    }

    // Add to browsing history
    public function addToHistory($productId, $userId = null, $browserId = null) {
        if (!$userId && !$browserId) return false;

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
        } elseif ($browserId) {
            $where[] = 'browser_id = :browser_id';
            $params[':browser_id'] = $browserId;
        } else {
            return false;
        }

        $query = "DELETE FROM {$this->table} WHERE " . implode(' AND ', $where);
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }
}
