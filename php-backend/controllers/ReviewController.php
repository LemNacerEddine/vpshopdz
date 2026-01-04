<?php
/**
 * Review Controller
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class ReviewController {
    private $db;
    private $review;
    private $product;
    private $auth;

    public function __construct($db) {
        $this->db = $db;
        $this->review = new Review($db);
        $this->product = new Product($db);
        $this->auth = new Auth();
    }

    // Get reviews for a product
    public function index($productId) {
        $reviews = $this->review->getByProduct($productId);
        jsonResponse($reviews);
    }

    // Create review
    public function store() {
        $user = $this->auth->requireAuth();
        $data = getJsonInput();

        if (empty($data['product_id']) || empty($data['rating'])) {
            errorResponse('المنتج والتقييم مطلوبان', 400);
        }

        if ($data['rating'] < 1 || $data['rating'] > 5) {
            errorResponse('التقييم يجب أن يكون بين 1 و 5', 400);
        }

        // Check if already reviewed
        if ($this->review->hasUserReviewed($data['product_id'], $user['user_id'])) {
            errorResponse('لقد قمت بتقييم هذا المنتج مسبقاً', 400);
        }

        $data['user_id'] = $user['user_id'];
        $data['user_name'] = $user['name'];

        $review = $this->review->create($data);

        // Update product rating
        $this->product->updateRating($data['product_id']);

        jsonResponse($review, 201);
    }
}
