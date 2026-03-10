<?php
/**
 * Review Model
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/helpers.php';

class Review {
    private $conn;
    private $table = 'reviews';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get reviews for a product
    public function getByProduct($productId, $limit = 50) {
        $query = "SELECT r.*, u.name as user_name, u.avatar as user_avatar 
                  FROM {$this->table} r
                  LEFT JOIN users u ON r.user_id = u.user_id
                  WHERE r.product_id = :product_id
                  ORDER BY r.created_at DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':product_id', $productId);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $reviews = $stmt->fetchAll();
        foreach ($reviews as &$review) {
            unset($review['id']);
        }
        
        return $reviews;
    }

    // Create review
    public function create($data) {
        $reviewId = generateId('review_');

        $query = "INSERT INTO {$this->table} (review_id, product_id, user_id, user_name, rating, comment)
                  VALUES (:review_id, :product_id, :user_id, :user_name, :rating, :comment)";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':review_id' => $reviewId,
            ':product_id' => $data['product_id'],
            ':user_id' => $data['user_id'],
            ':user_name' => $data['user_name'] ?? null,
            ':rating' => $data['rating'],
            ':comment' => $data['comment'] ?? null
        ]);

        return $this->findById($reviewId);
    }

    // Find by ID
    public function findById($reviewId) {
        $query = "SELECT * FROM {$this->table} WHERE review_id = :review_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':review_id' => $reviewId]);
        $review = $stmt->fetch();
        
        if ($review) {
            unset($review['id']);
        }
        
        return $review;
    }

    // Check if user already reviewed
    public function hasUserReviewed($productId, $userId) {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE product_id = :product_id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':product_id' => $productId, ':user_id' => $userId]);
        return $stmt->fetch()['count'] > 0;
    }

    // Delete review
    public function delete($reviewId) {
        $query = "DELETE FROM {$this->table} WHERE review_id = :review_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':review_id' => $reviewId]);
    }
}
