<?php
/**
 * Product Controller
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class ProductController {
    private $db;
    private $product;
    private $auth;

    public function __construct($db) {
        $this->db = $db;
        $this->product = new Product($db);
        $this->auth = new Auth();
    }

    // Get all products
    public function index() {
        $filters = [
            'category' => $_GET['category'] ?? $_GET['category_id'] ?? null,
            'search' => $_GET['search'] ?? null,
            'featured' => isset($_GET['featured']),
            'limit' => $_GET['limit'] ?? 50,
            'offset' => $_GET['offset'] ?? 0
        ];

        $products = $this->product->getAll($filters);
        jsonResponse($products);
    }

    // Get single product
    public function show($productId) {
        $product = $this->product->findById($productId);
        
        if (!$product) {
            errorResponse('المنتج غير موجود', 404);
        }

        jsonResponse($product);
    }

    // Get products on sale
    public function onSale() {
        $limit = $_GET['limit'] ?? 20;
        $products = $this->product->getOnSale($limit);
        jsonResponse($products);
    }

    // Create product (admin only)
    public function store() {
        $this->auth->requireAdmin();
        $data = getJsonInput();

        if (empty($data['name_ar']) || !isset($data['price'])) {
            errorResponse('الاسم والسعر مطلوبان', 400);
        }

        $product = $this->product->create($data);
        jsonResponse($product, 201);
    }

    // Update product (admin only)
    public function update($productId) {
        $this->auth->requireAdmin();
        $data = getJsonInput();

        $existing = $this->product->findById($productId);
        if (!$existing) {
            errorResponse('المنتج غير موجود', 404);
        }

        $product = $this->product->update($productId, $data);
        jsonResponse($product);
    }

    // Delete product (admin only)
    public function destroy($productId) {
        $this->auth->requireAdmin();

        $existing = $this->product->findById($productId);
        if (!$existing) {
            errorResponse('المنتج غير موجود', 404);
        }

        $this->product->delete($productId);
        jsonResponse(['message' => 'تم حذف المنتج بنجاح']);
    }
}
