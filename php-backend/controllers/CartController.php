<?php
/**
 * Cart Controller
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class CartController {
    private $db;
    private $cart;
    private $auth;

    public function __construct($db) {
        $this->db = $db;
        $this->cart = new Cart($db);
        $this->auth = new Auth();
    }

    // Get cart
    public function index() {
        $user = $this->auth->getCurrentUser();
        $browserId = $_GET['browser_id'] ?? $_COOKIE['browser_id'] ?? null;
        
        $userId = $user ? $user['user_id'] : null;
        
        $cart = $this->cart->getCart($userId, $browserId);
        jsonResponse($cart);
    }

    // Add to cart
    public function add() {
        $data = getJsonInput();
        $user = $this->auth->getCurrentUser();
        $browserId = $data['browser_id'] ?? $_COOKIE['browser_id'] ?? null;
        
        if (empty($data['product_id'])) {
            errorResponse('المنتج مطلوب', 400);
        }

        $userId = $user ? $user['user_id'] : null;
        $quantity = $data['quantity'] ?? 1;
        
        $cart = $this->cart->addItem($userId, $browserId, $data['product_id'], $quantity);
        jsonResponse($cart);
    }

    // Update cart item
    public function update() {
        $data = getJsonInput();
        $user = $this->auth->getCurrentUser();
        $browserId = $data['browser_id'] ?? $_COOKIE['browser_id'] ?? null;
        
        if (empty($data['product_id']) || !isset($data['quantity'])) {
            errorResponse('المنتج والكمية مطلوبان', 400);
        }

        $userId = $user ? $user['user_id'] : null;
        
        $cart = $this->cart->updateItem($userId, $browserId, $data['product_id'], $data['quantity']);
        jsonResponse($cart);
    }

    // Remove from cart
    public function remove($productId) {
        $user = $this->auth->getCurrentUser();
        $browserId = $_GET['browser_id'] ?? $_COOKIE['browser_id'] ?? null;
        
        $userId = $user ? $user['user_id'] : null;
        
        $cart = $this->cart->removeItem($userId, $browserId, $productId);
        jsonResponse($cart);
    }

    // Clear cart
    public function clear() {
        $user = $this->auth->getCurrentUser();
        $data = getJsonInput();
        $browserId = $data['browser_id'] ?? $_COOKIE['browser_id'] ?? null;
        
        $userId = $user ? $user['user_id'] : null;
        
        $cart = $this->cart->clearCart($userId, $browserId);
        jsonResponse($cart);
    }
}
