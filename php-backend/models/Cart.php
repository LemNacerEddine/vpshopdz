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
            return ['cart_id' => null, 'items' => []];
        }

        // Get cart ID first
        $cartQuery = "SELECT id FROM {$this->table} WHERE " .
            ($userId ? "user_id = :user_id" : "browser_id = :browser_id") . " LIMIT 1";
        $cartStmt = $this->conn->prepare($cartQuery);
        $cartStmt->execute($params);
        $cartRow = $cartStmt->fetch();

        if (!$cartRow) {
            return ['cart_id' => null, 'items' => []];
        }

        $query = "SELECT ci.product_id, ci.quantity,
                         p.product_id, p.name_ar, p.name_fr, p.name_en,
                         p.description_ar, p.description_fr, p.description_en,
                         p.price, p.old_price, p.stock, p.category_id, p.featured, p.unit,
                         p.discount_percent, p.discount_start, p.discount_end,
                         p.rating, p.reviews_count, p.created_at,
                         p.shipping_type, p.fixed_shipping_price, p.weight,
                         p.length, p.width, p.height
                  FROM cart_items ci
                  JOIN carts c ON ci.cart_id = c.id
                  JOIN products p ON ci.product_id = p.product_id
                  WHERE " . implode(' AND ', $where);

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $items = [];
        foreach ($rows as $row) {
            // Get product images
            $imgQuery = "SELECT image_url FROM product_images WHERE product_id = :product_id ORDER BY sort_order";
            $imgStmt = $this->conn->prepare($imgQuery);
            $imgStmt->execute([':product_id' => $row['product_id']]);
            $images = $imgStmt->fetchAll(PDO::FETCH_COLUMN);

            // If no images in product_images table, check if product has images field
            if (empty($images)) {
                // Try to get from products table if there's an images column
                $images = [];
            }

            // Calculate final price with discount
            $price = (float)$row['price'];
            $finalPrice = $price;
            if ($row['discount_percent'] > 0) {
                $now = new DateTime();
                $start = $row['discount_start'] ? new DateTime($row['discount_start']) : null;
                $end = $row['discount_end'] ? new DateTime($row['discount_end']) : null;

                if ((!$start || $now >= $start) && (!$end || $now <= $end)) {
                    $finalPrice = $price * (1 - $row['discount_percent'] / 100);
                }
            }

            $items[] = [
                'product_id' => $row['product_id'],
                'quantity' => (int)$row['quantity'],
                'product' => [
                    'product_id' => $row['product_id'],
                    'name_ar' => $row['name_ar'],
                    'name_fr' => $row['name_fr'],
                    'name_en' => $row['name_en'],
                    'description_ar' => $row['description_ar'],
                    'description_fr' => $row['description_fr'],
                    'description_en' => $row['description_en'],
                    'price' => $finalPrice,
                    'original_price' => $price,
                    'old_price' => $row['old_price'] ? (float)$row['old_price'] : null,
                    'stock' => (int)$row['stock'],
                    'category_id' => $row['category_id'],
                    'images' => $images,
                    'featured' => (bool)$row['featured'],
                    'unit' => $row['unit'],
                    'discount_percent' => $row['discount_percent'] ? (int)$row['discount_percent'] : null,
                    'discount_start' => $row['discount_start'],
                    'discount_end' => $row['discount_end'],
                    'rating' => $row['rating'] ? (float)$row['rating'] : 0,
                    'reviews_count' => (int)$row['reviews_count'],
                    'created_at' => $row['created_at'],
                    'shipping_type' => $row['shipping_type'] ?? 'standard',
                    'fixed_shipping_price' => $row['fixed_shipping_price'] ? (float)$row['fixed_shipping_price'] : null,
                    'weight' => $row['weight'] ? (float)$row['weight'] : 0,
                    'length' => $row['length'] ? (float)$row['length'] : 0,
                    'width' => $row['width'] ? (float)$row['width'] : 0,
                    'height' => $row['height'] ? (float)$row['height'] : 0
                ]
            ];
        }

        // Generate cart_id string
        $cartId = 'cart_' . substr(md5($cartRow['id']), 0, 8);

        return ['cart_id' => $cartId, 'items' => $items];
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
        if (!$cartId) return ['message' => 'لم يتم العثور على السلة', 'items' => []];

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

        return ['message' => 'تمت إضافة المنتج للسلة'];
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

        return ['message' => 'تم حذف المنتج من السلة'];
    }

    // Clear cart
    public function clearCart($userId, $browserId) {
        $cartId = $this->getOrCreateCart($userId, $browserId);
        if (!$cartId) return false;

        $query = "DELETE FROM cart_items WHERE cart_id = :cart_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':cart_id' => $cartId]);

        return ['cart_id' => null, 'items' => []];
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
