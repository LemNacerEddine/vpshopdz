<?php
/**
 * Order Controller
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Address.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/OrderShipping.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class OrderController {
    private $db;
    private $order;
    private $address;
    private $cart;
    private $orderShipping;
    private $auth;

    public function __construct($db) {
        $this->db = $db;
        $this->order = new Order($db);
        $this->address = new Address($db);
        $this->cart = new Cart($db);
        $this->orderShipping = new OrderShipping($db);
        $this->auth = new Auth();
    }

    // Get all orders (admin sees all, user sees theirs)
    public function index() {
        $user = $this->auth->requireAuth();
        
        $filters = [
            'status' => $_GET['status'] ?? null,
            'limit' => $_GET['limit'] ?? 50
        ];

        // If not admin, force user_id filter
        if ($user['role'] !== 'admin') {
            $filters['user_id'] = $user['user_id'];
        }

        $orders = $this->order->getAll($filters);
        jsonResponse($orders);
    }

    // Get user's orders
    public function myOrders() {
        $user = $this->auth->requireAuth();
        
        $orders = $this->order->getAll(['user_id' => $user['user_id']]);
        jsonResponse($orders);
    }

    // Get single order
    public function show($orderId) {
        $user = $this->auth->getCurrentUser();
        $order = $this->order->findById($orderId);
        
        if (!$order) {
            errorResponse('الطلب غير موجود', 404);
        }

        // Check access
        if ($user && $user['role'] !== 'admin' && $order['user_id'] !== $user['user_id']) {
            errorResponse('غير مصرح بالوصول', 403);
        }

        jsonResponse($order);
    }

    // Create order
    public function store() {
        $data = getJsonInput();
        $user = $this->auth->getCurrentUser();

        // Auto-fill data for logged-in users if missing
        if ($user) {
            // Fill user_id
            $data['user_id'] = $user['user_id'];

            // Fill basic info from profile if missing
            if (empty($data['customer_name']) && !empty($user['name'])) {
                $data['customer_name'] = $user['name'];
            }
            if (empty($data['customer_phone']) && !empty($user['phone'])) {
                $data['customer_phone'] = $user['phone'];
            }
            if (empty($data['customer_email']) && !empty($user['email'])) {
                $data['customer_email'] = $user['email'];
            }

            // Handle Address
            // 1. If address_id is provided, use that address
            // 2. If no address provided at all, try to find default address
            if (!empty($data['address_id']) || empty($data['shipping_address'])) {
                $address = null;
                
                if (!empty($data['address_id'])) {
                    $address = $this->address->findById($data['address_id']);
                    // Verify ownership
                    if ($address && $address['user_id'] !== $user['user_id']) {
                        $address = null;
                    }
                } else {
                    // Get default address
                    $addresses = $this->address->getByUser($user['user_id']);
                    if (!empty($addresses)) {
                        // First one is default/newest due to ordering in model
                        $address = $addresses[0];
                    }
                }

                // If we found a valid address, use it to fill missing fields
                if ($address) {
                    if (empty($data['shipping_address'])) {
                        $data['shipping_address'] = $address['address_line'];
                        // Add commune/wilaya if not in address line to be safe, or just rely on what we have
                        if (!empty($address['commune']) || !empty($address['wilaya'])) {
                            $data['shipping_address'] .= ' - ' . ($address['commune'] ?? '') . ' - ' . ($address['wilaya'] ?? '');
                        }
                    }
                    if (empty($data['wilaya']) && !empty($address['wilaya'])) {
                        $data['wilaya'] = $address['wilaya'];
                    }
                    // Also useful if profile didn't have phone but address does
                    if (empty($data['customer_phone']) && !empty($address['phone'])) {
                        $data['customer_phone'] = $address['phone'];
                    }
                    if (empty($data['customer_name']) && !empty($address['full_name'])) {
                        $data['customer_name'] = $address['full_name'];
                    }
                }
            }

            // Handle missing items by fetching from Cart
            if (empty($data['items']) || !is_array($data['items'])) {
                $cartData = $this->cart->getCart($user['user_id']);
                if (!empty($cartData['items'])) {
                    $cartItems = [];
                    $total = 0;

                    foreach ($cartData['items'] as $item) {
                        $product = $item['product'];
                        $price = $product['price']; // This is the final calculated price from Cart model
                        $quantity = $item['quantity'];
                        
                        $cartItems[] = [
                            'product_id' => $item['product_id'],
                            'product_name' => $product['name_ar'], // Defaulting to Arabic name
                            'product_image' => !empty($product['images']) ? $product['images'][0] : null,
                            'quantity' => $quantity,
                            'price' => $price
                        ];
                        
                        $total += $price * $quantity;
                    }

                    $data['items'] = $cartItems;
                    $data['subtotal'] = $total;
                    if (!isset($data['total'])) {
                        $data['total'] = $total;
                    }
                }
            }

            // Fill commune from address if not provided
            if (empty($data['commune']) && !empty($address) && !empty($address['commune'])) {
                $data['commune'] = $address['commune'];
            }
        }

        // Calculate shipping cost server-side if shipping company provided
        if (!empty($data['shipping_company_id']) && !empty($data['wilaya'])) {
            require_once __DIR__ . '/../controllers/ShippingController.php';
            $shippingController = new ShippingController($this->db);

            // Use items from payload or from cart
            $shippingItems = null;
            if ($user) {
                $cartData = $this->cart->getCart($user['user_id']);
                if ($cartData && !empty($cartData['items'])) {
                    $shippingItems = $cartData['items'];
                }
            }

            if ($shippingItems) {
                $shippingResult = $shippingController->calculateShippingOptions(
                    $shippingItems,
                    $data['wilaya'],
                    $data['commune'] ?? null,
                    $data['shipping_type'] ?? 'home'
                );

                // Find the selected company's cost
                $selectedOption = null;
                foreach ($shippingResult['options'] as $option) {
                    if ($option['company_id'] === $data['shipping_company_id']) {
                        $selectedOption = $option;
                        break;
                    }
                }

                // Check for free shipping (from shipping rules OR recovery offer)
                $freeShippingFromOffer = isset($data['free_shipping_offer']) && $data['free_shipping_offer'];
                if ($shippingResult['free_shipping'] || $freeShippingFromOffer) {
                    $data['shipping_cost'] = 0;
                } elseif ($selectedOption) {
                    $data['shipping_cost'] = $selectedOption['shipping_cost'];
                }
            }
        }

        // Ensure subtotal is set correctly (original price before discount)
        $subtotal = $data['subtotal'] ?? $data['total'] ?? 0;
        $data['subtotal'] = $subtotal;
        $discountAmount = floatval($data['discount_amount'] ?? 0);
        $data['discount_percentage'] = floatval($data['discount_percentage'] ?? 0);
        $shippingCost = floatval($data['shipping_cost'] ?? 0);

        // Recalculate total: subtotal - discount + shipping
        $data['total'] = max(0, $subtotal - $discountAmount) + $shippingCost;

        // Validation
        if (empty($data['customer_name']) || empty($data['customer_phone']) || empty($data['shipping_address'])) {
            errorResponse('الاسم ورقم الهاتف والعنوان مطلوبون', 400);
        }

        if (empty($data['items']) || !is_array($data['items'])) {
            errorResponse('يجب إضافة منتجات للطلب', 400);
        }

        $order = $this->order->create($data);

        // Create order_shipping record if shipping company provided
        if ($order && !empty($data['shipping_company_id'])) {
            $this->orderShipping->create([
                'order_id' => $order['order_id'],
                'company_id' => $data['shipping_company_id'],
                'shipping_type' => $data['shipping_type'] ?? 'home',
                'total_weight' => $selectedOption['total_weight'] ?? 0,
                'billable_weight' => $selectedOption['billable_weight'] ?? 0,
                'shipping_cost' => $data['shipping_cost'] ?? 0
            ]);
        }

        // Clear cart after successful order
        if ($order) {
            if ($user) {
                $this->cart->clearCart($user['user_id'], null);
            }
            // Also clear browser cart to prevent stale items
            $browserId = $data['browser_id'] ?? $_COOKIE['browser_id'] ?? null;
            if ($browserId) {
                $this->cart->clearCart(null, $browserId);
            }
        }

        jsonResponse($order, 201);
    }

    // Update order status (admin only)
    public function updateStatus($orderId) {
        $this->auth->requireAdmin();
        $data = getJsonInput();

        if (empty($data['status'])) {
            errorResponse('الحالة مطلوبة', 400);
        }

        $existing = $this->order->findById($orderId);
        if (!$existing) {
            errorResponse('الطلب غير موجود', 404);
        }

        $order = $this->order->updateStatus($orderId, $data['status']);

        if (!$order) {
            errorResponse('حالة غير صالحة', 400);
        }

        // Update tracking number if provided (when status = shipped)
        if (!empty($data['tracking_number'])) {
            $this->orderShipping->updateTracking($orderId, $data['tracking_number']);
        }

        jsonResponse($order);
    }
}
