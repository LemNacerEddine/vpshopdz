<?php
/**
 * Category Controller
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class CategoryController {
    private $db;
    private $category;
    private $auth;

    public function __construct($db) {
        $this->db = $db;
        $this->category = new Category($db);
        $this->auth = new Auth();
    }

    // Get all categories
    public function index() {
        $categories = $this->category->getAll();
        jsonResponse($categories);
    }

    // Get single category
    public function show($categoryId) {
        $category = $this->category->findById($categoryId);
        
        if (!$category) {
            errorResponse('القسم غير موجود', 404);
        }

        jsonResponse($category);
    }

    // Create category (admin only)
    public function store() {
        $this->auth->requireAdmin();
        $data = getJsonInput();

        if (empty($data['name_ar'])) {
            errorResponse('اسم القسم مطلوب', 400);
        }

        $category = $this->category->create($data);
        jsonResponse($category, 201);
    }

    // Update category (admin only)
    public function update($categoryId) {
        $this->auth->requireAdmin();
        $data = getJsonInput();

        $existing = $this->category->findById($categoryId);
        if (!$existing) {
            errorResponse('القسم غير موجود', 404);
        }

        $category = $this->category->update($categoryId, $data);
        jsonResponse($category);
    }

    // Delete category (admin only)
    public function destroy($categoryId) {
        $this->auth->requireAdmin();

        $existing = $this->category->findById($categoryId);
        if (!$existing) {
            errorResponse('القسم غير موجود', 404);
        }

        $this->category->delete($categoryId);
        jsonResponse(['message' => 'تم حذف القسم بنجاح']);
    }
}
