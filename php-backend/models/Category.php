<?php
/**
 * Category Model
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/helpers.php';

class Category {
    private $conn;
    private $table = 'categories';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all categories
    public function getAll() {
        $query = "SELECT * FROM {$this->table} ORDER BY name_ar";
        $stmt = $this->conn->query($query);
        $categories = $stmt->fetchAll();
        
        foreach ($categories as &$cat) {
            unset($cat['id']);
        }
        
        return $categories;
    }

    // Get single category
    public function findById($categoryId) {
        $query = "SELECT * FROM {$this->table} WHERE category_id = :category_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':category_id' => $categoryId]);
        $category = $stmt->fetch();
        
        if ($category) {
            unset($category['id']);
        }
        
        return $category;
    }

    // Create category
    public function create($data) {
        $categoryId = generateId('cat_');

        $query = "INSERT INTO {$this->table} 
                  (category_id, name_ar, name_fr, name_en, description_ar, description_fr, description_en, icon, image)
                  VALUES (:category_id, :name_ar, :name_fr, :name_en, :description_ar, :description_fr, :description_en, :icon, :image)";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':category_id' => $categoryId,
            ':name_ar' => $data['name_ar'],
            ':name_fr' => $data['name_fr'] ?? null,
            ':name_en' => $data['name_en'] ?? null,
            ':description_ar' => $data['description_ar'] ?? null,
            ':description_fr' => $data['description_fr'] ?? null,
            ':description_en' => $data['description_en'] ?? null,
            ':icon' => $data['icon'] ?? 'Leaf',
            ':image' => $data['image'] ?? null
        ]);

        return $this->findById($categoryId);
    }

    // Update category
    public function update($categoryId, $data) {
        $fields = [];
        $params = [':category_id' => $categoryId];

        $allowedFields = ['name_ar', 'name_fr', 'name_en', 'description_ar', 'description_fr', 
                          'description_en', 'icon', 'image'];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (!empty($fields)) {
            $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE category_id = :category_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
        }

        return $this->findById($categoryId);
    }

    // Delete category
    public function delete($categoryId) {
        $query = "DELETE FROM {$this->table} WHERE category_id = :category_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':category_id' => $categoryId]);
    }

    // Count categories
    public function count() {
        $query = "SELECT COUNT(*) as count FROM {$this->table}";
        $stmt = $this->conn->query($query);
        return $stmt->fetch()['count'];
    }
}
