<?php
/**
 * Cart Model
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/helpers.php';

class Cart {
    private $conn;
    private $table = 'carts';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get cart by user or browser
    public function getCart($userId = null, $browserId = null) {
        $where = [];
        $params = [];

        if ($userId) {
            $where[] = 'c.user_id = :user_id';
            $params[':user_id'] = $userId;
        } elseif ($browserId) {
            $where[] = 'c.browser_id = :browser_id';
            $params[':browser_id'] = $browserId;
        } else {
            return ['items' => [], 'total' => 0];
        }

        $query = "SELECT ci.*, p.name_ar, p.name_fr, p.name_en, p.price, p.stock,
                         p.discount_percent, p.discount_start, p.discount_end,
                         (SELECT image_url FROM product_images WHERE product_id = p.product_id ORDER BY sort_order LIMIT 1) as image
                  FROM cart_items ci
                  JOIN carts c ON ci.cart_id = c.id
                  JOIN products p ON ci.product_id = p.product_id
                  WHERE " . implode(' AND ', $where);

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        $total = 0;
        foreach ($items as &$item) {
            // Calculate price with discount
            $price = $item['price'];
            if ($item['discount_percent'] > 0) {
                $now = new DateTime();
                $start = $item['discount_start'] ? new DateTime($item['discount_start']) : null;
                $end = $item['discount_end'] ? new DateTime($item['discount_end']) : null;
                
                if ((!$start || $now >= $start) && (!$end || $now <= $end)) {
                    $price = $price * (1 - $item['discount_percent'] / 100);
                }
            }
            $item['final_price'] = $price;
            $item['subtotal'] = $price * $item['quantity'];
            $total += $item['subtotal'];
            unset($item['id']);
        }

        return ['items' => $items, 'total' => $total];
    }

    // Get or create cart
    private function getOrCreateCart($userId = null, $browserId = null) {
        $where = [];
        $params = [];

        if ($userId) {
            $where[] = 'user_id = :user_id';
            $params[':user_id'] = $userId;
        } elseif ($browserId) {
            $where[] = 'browser_id = :browser_id';
            $params[':browser_id'] = $browserId;
        } else {
            return null;
        }

        // Check existing
        $query = "SELECT id FROM {$this->table} WHERE " . implode(' AND ', $where) . " LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $cart = $stmt->fetch();

        if ($cart) {
            return $cart['id'];
        }

        // Create new
        $query = "INSERT INTO {$this->table} (user_id, browser_id) VALUES (:user_id, :browser_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':user_id' => $userId,
            ':browser_id' => $browserId
        ]);

        return $this->conn->lastInsertId();
    }

    // Add item to cart
    public function addItem($userId, $browserId, $productId, $quantity = 1) {
        $cartId = $this->getOrCreateCart($userId, $browserId);
        if (!$cartId) return false;

        // Check if item exists
        $query = "SELECT id, quantity FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':cart_id' => $cartId, ':product_id' => $productId]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update quantity
            $query = "UPDATE cart_items SET quantity = quantity + :quantity WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':quantity' => $quantity, ':id' => $existing['id']]);
        } else {
            // Insert new
            $query = "INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (:cart_id, :product_id, :quantity)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':cart_id' => $cartId, ':product_id' => $productId, ':quantity' => $quantity]);
        }

        return $this->getCart($userId, $browserId);
    }

    // Update item quantity
    public function updateItem($userId, $browserId, $productId, $quantity) {
        $cartId = $this->getOrCreateCart($userId, $browserId);
        if (!$cartId) return false;

        if ($quantity <= 0) {
            return $this->removeItem($userId, $browserId, $productId);
        }

        $query = "UPDATE cart_items SET quantity = :quantity WHERE cart_id = :cart_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':quantity' => $quantity, ':cart_id' => $cartId, ':product_id' => $productId]);

        return $this->getCart($userId, $browserId);
    }

    // Remove item from cart
    public function removeItem($userId, $browserId, $productId) {
        $cartId = $this->getOrCreateCart($userId, $browserId);
        if (!$cartId) return false;

        $query = "DELETE FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':cart_id' => $cartId, ':product_id' => $productId]);

        return $this->getCart($userId, $browserId);
    }

    // Clear cart
    public function clearCart($userId, $browserId) {
        $cartId = $this->getOrCreateCart($userId, $browserId);
        if (!$cartId) return false;

        $query = "DELETE FROM cart_items WHERE cart_id = :cart_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':cart_id' => $cartId]);

        return ['items' => [], 'total' => 0];
    }

    // Merge guest cart to user cart
    public function mergeCart($userId, $browserId) {
        if (!$userId || !$browserId) return;

        // Get guest cart
        $guestQuery = "SELECT id FROM {$this->table} WHERE browser_id = :browser_id AND user_id IS NULL";
        $stmt = $this->conn->prepare($guestQuery);
        $stmt->execute([':browser_id' => $browserId]);
        $guestCart = $stmt->fetch();

        if (!$guestCart) return;

        // Get or create user cart
        $userCartId = $this->getOrCreateCart($userId, null);

        // Merge items
        $query = "INSERT INTO cart_items (cart_id, product_id, quantity)
                  SELECT :user_cart_id, product_id, quantity FROM cart_items WHERE cart_id = :guest_cart_id
                  ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_cart_id' => $userCartId, ':guest_cart_id' => $guestCart['id']]);

        // Delete guest cart
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $guestCart['id']]);
    }
}
