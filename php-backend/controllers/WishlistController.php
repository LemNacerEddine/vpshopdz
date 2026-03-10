<?php
/**
 * Wishlist Controller
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../models/Wishlist.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class WishlistController {
    private $db;
    private $wishlist;
    private $auth;

    public function __construct($db) {
        $this->db = $db;
        $this->wishlist = new Wishlist($db);
        $this->auth = new Auth();
    }

    // Get user's wishlist
    public function index() {
        $user = $this->auth->requireAuth();
        $products = $this->wishlist->getByUser($user['user_id']);
        jsonResponse($products);
    }

    // Add to wishlist
    public function add($productId) {
        $user = $this->auth->requireAuth();
        $this->wishlist->add($user['user_id'], $productId);
        jsonResponse(['message' => 'تمت الإضافة إلى المفضلة']);
    }

    // Remove from wishlist
    public function remove($productId) {
        $user = $this->auth->requireAuth();
        $this->wishlist->remove($user['user_id'], $productId);
        jsonResponse(['message' => 'تمت الإزالة من المفضلة']);
    }
}
