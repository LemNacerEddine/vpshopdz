<?php
/**
 * Shipping Company Model
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/helpers.php';

class ShippingCompany {
    private $conn;
    private $table = 'shipping_companies';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($onlyActive = false) {
        $query = "SELECT * FROM {$this->table}";
        if ($onlyActive) {
            $query .= " WHERE is_active = 1";
        }
        $query .= " ORDER BY sort_order ASC, created_at DESC";
        $stmt = $this->conn->query($query);
        $companies = $stmt->fetchAll();
        foreach ($companies as &$c) {
            unset($c['id']);
        }
        return $companies;
    }

    public function findById($companyId) {
        $query = "SELECT * FROM {$this->table} WHERE company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':company_id' => $companyId]);
        $company = $stmt->fetch();
        if ($company) unset($company['id']);
        return $company;
    }

    public function create($data) {
        $companyId = generateId('ship_co_');
        $query = "INSERT INTO {$this->table}
                  (company_id, name_ar, name_fr, name_en, logo, phone, email, website,
                   tracking_url_template, volumetric_divisor, included_weight, additional_price_per_kg, is_active, sort_order)
                  VALUES
                  (:company_id, :name_ar, :name_fr, :name_en, :logo, :phone, :email, :website,
                   :tracking_url_template, :volumetric_divisor, :included_weight, :additional_price_per_kg, :is_active, :sort_order)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':company_id' => $companyId,
            ':name_ar' => $data['name_ar'],
            ':name_fr' => $data['name_fr'] ?? null,
            ':name_en' => $data['name_en'] ?? null,
            ':logo' => $data['logo'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':email' => $data['email'] ?? null,
            ':website' => $data['website'] ?? null,
            ':tracking_url_template' => $data['tracking_url_template'] ?? null,
            ':volumetric_divisor' => $data['volumetric_divisor'] ?? 5000,
            ':included_weight' => $data['included_weight'] ?? 5.00,
            ':additional_price_per_kg' => $data['additional_price_per_kg'] ?? 0,
            ':is_active' => $data['is_active'] ?? true,
            ':sort_order' => $data['sort_order'] ?? 0
        ]);
        return $this->findById($companyId);
    }

    public function update($companyId, $data) {
        $existing = $this->findById($companyId);
        if (!$existing) return null;

        $fields = [];
        $params = [':company_id' => $companyId];
        $allowedFields = ['name_ar', 'name_fr', 'name_en', 'logo', 'phone', 'email',
                          'website', 'tracking_url_template', 'volumetric_divisor',
                          'included_weight', 'additional_price_per_kg', 'is_active', 'sort_order'];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }
        if (!empty($fields)) {
            $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE company_id = :company_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
        }
        return $this->findById($companyId);
    }

    public function delete($companyId) {
        $query = "DELETE FROM {$this->table} WHERE company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':company_id' => $companyId]);
    }
}
