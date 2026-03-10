<?php
/**
 * Product Model
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/helpers.php';

class Product {
    private $conn;
    private $table = 'products';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all products with filters
    public function getAll($filters = []) {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['category'])) {
            $where[] = 'p.category_id = :category';
            $params[':category'] = $filters['category'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(p.name_ar LIKE :search OR p.name_fr LIKE :search OR p.name_en LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['featured'])) {
            $where[] = 'p.featured = 1';
        }

        $limit = isset($filters['limit']) ? (int)$filters['limit'] : 50;
        $offset = isset($filters['offset']) ? (int)$filters['offset'] : 0;

        $query = "SELECT p.*, GROUP_CONCAT(pi.image_url ORDER BY pi.sort_order) as images_str
                  FROM {$this->table} p
                  LEFT JOIN product_images pi ON p.product_id = pi.product_id
                  WHERE " . implode(' AND ', $where) . "
                  GROUP BY p.product_id
                  ORDER BY p.created_at DESC
                  LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        // Process images
        foreach ($products as &$product) {
            $product['images'] = $product['images_str'] ? explode(',', $product['images_str']) : [];
            unset($product['images_str']);
            unset($product['id']);
        }

        return $products;
    }

    // Get single product
    public function findById($productId) {
        $query = "SELECT p.*, GROUP_CONCAT(pi.image_url ORDER BY pi.sort_order) as images_str
                  FROM {$this->table} p
                  LEFT JOIN product_images pi ON p.product_id = pi.product_id
                  WHERE p.product_id = :product_id
                  GROUP BY p.product_id";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':product_id' => $productId]);
        $product = $stmt->fetch();

        if ($product) {
            $product['images'] = $product['images_str'] ? explode(',', $product['images_str']) : [];
            unset($product['images_str']);
            unset($product['id']);
        }

        return $product;
    }

    // Get products on sale
    public function getOnSale($limit = 20) {
        $query = "SELECT p.*, GROUP_CONCAT(pi.image_url ORDER BY pi.sort_order) as images_str
                  FROM {$this->table} p
                  LEFT JOIN product_images pi ON p.product_id = pi.product_id
                  WHERE p.discount_percent > 0 
                  AND (p.discount_start IS NULL OR p.discount_start <= NOW())
                  AND (p.discount_end IS NULL OR p.discount_end >= NOW())
                  GROUP BY p.product_id
                  ORDER BY p.discount_percent DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
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

    // Create product
    public function create($data) {
        $productId = generateId('prod_');

        $query = "INSERT INTO {$this->table} 
                  (product_id, name_ar, name_fr, name_en, description_ar, description_fr, description_en,
                   price, old_price, discount_percent, discount_start, discount_end, stock, unit, 
                   category_id, featured)
                  VALUES 
                  (:product_id, :name_ar, :name_fr, :name_en, :description_ar, :description_fr, :description_en,
                   :price, :old_price, :discount_percent, :discount_start, :discount_end, :stock, :unit,
                   :category_id, :featured)";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':product_id' => $productId,
            ':name_ar' => $data['name_ar'],
            ':name_fr' => $data['name_fr'] ?? null,
            ':name_en' => $data['name_en'] ?? null,
            ':description_ar' => $data['description_ar'] ?? null,
            ':description_fr' => $data['description_fr'] ?? null,
            ':description_en' => $data['description_en'] ?? null,
            ':price' => $data['price'],
            ':old_price' => $data['old_price'] ?? null,
            ':discount_percent' => $data['discount_percent'] ?? 0,
            ':discount_start' => $data['discount_start'] ?? null,
            ':discount_end' => $data['discount_end'] ?? null,
            ':stock' => $data['stock'] ?? 0,
            ':unit' => $data['unit'] ?? 'piece',
            ':category_id' => $data['category_id'] ?? null,
            ':featured' => $data['featured'] ?? false
        ]);

        // Add images
        if (!empty($data['images'])) {
            $this->addImages($productId, $data['images']);
        }

        return $this->findById($productId);
    }

    // Update product
    public function update($productId, $data) {
        $fields = [];
        $params = [':product_id' => $productId];

        $allowedFields = ['name_ar', 'name_fr', 'name_en', 'description_ar', 'description_fr', 
                          'description_en', 'price', 'old_price', 'discount_percent', 'discount_start',
                          'discount_end', 'stock', 'unit', 'category_id', 'featured'];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (!empty($fields)) {
            $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE product_id = :product_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
        }

        // Update images if provided
        if (isset($data['images'])) {
            $this->deleteImages($productId);
            if (!empty($data['images'])) {
                $this->addImages($productId, $data['images']);
            }
        }

        return $this->findById($productId);
    }

    // Delete product
    public function delete($productId) {
        $query = "DELETE FROM {$this->table} WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':product_id' => $productId]);
    }

    // Add images
    private function addImages($productId, $images) {
        $query = "INSERT INTO product_images (product_id, image_url, sort_order) VALUES (:product_id, :image_url, :sort_order)";
        $stmt = $this->conn->prepare($query);

        foreach ($images as $index => $imageUrl) {
            $stmt->execute([
                ':product_id' => $productId,
                ':image_url' => $imageUrl,
                ':sort_order' => $index
            ]);
        }
    }

    // Delete images
    private function deleteImages($productId) {
        $query = "DELETE FROM product_images WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':product_id' => $productId]);
    }

    // Update rating
    public function updateRating($productId) {
        $query = "UPDATE {$this->table} p SET 
                  rating = (SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE product_id = :pid1),
                  reviews_count = (SELECT COUNT(*) FROM reviews WHERE product_id = :pid2)
                  WHERE product_id = :pid3";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':pid1' => $productId, ':pid2' => $productId, ':pid3' => $productId]);
    }

    // Count products
    public function count() {
        $query = "SELECT COUNT(*) as count FROM {$this->table}";
        $stmt = $this->conn->query($query);
        return $stmt->fetch()['count'];
    }
}
