<?php
/**
 * Address Model
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/helpers.php';

class Address {
    private $conn;
    private $table = 'addresses';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get user addresses
    public function getByUser($userId) {
        $query = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY is_default DESC, created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $userId]);
        $addresses = $stmt->fetchAll();
        
        foreach ($addresses as &$addr) {
            unset($addr['id']);
        }
        
        return $addresses;
    }

    // Get single address
    public function findById($addressId) {
        $query = "SELECT * FROM {$this->table} WHERE address_id = :address_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':address_id' => $addressId]);
        $address = $stmt->fetch();
        
        if ($address) {
            unset($address['id']);
        }
        
        return $address;
    }

    // Create address
    public function create($userId, $data) {
        $addressId = generateId('addr_');

        // If this is default, unset others
        if (!empty($data['is_default'])) {
            $this->unsetDefaultAddresses($userId);
        }

        $query = "INSERT INTO {$this->table} 
                  (address_id, user_id, label, full_name, phone, wilaya, commune, address_line, is_default)
                  VALUES (:address_id, :user_id, :label, :full_name, :phone, :wilaya, :commune, :address_line, :is_default)";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':address_id' => $addressId,
            ':user_id' => $userId,
            ':label' => $data['label'] ?? 'المنزل',
            ':full_name' => $data['full_name'],
            ':phone' => $data['phone'],
            ':wilaya' => $data['wilaya'],
            ':commune' => $data['commune'] ?? null,
            ':address_line' => $data['address_line'],
            ':is_default' => $data['is_default'] ?? false
        ]);

        return $this->findById($addressId);
    }

    // Update address
    public function update($addressId, $userId, $data) {
        // Verify ownership
        $existing = $this->findById($addressId);
        if (!$existing || $existing['user_id'] !== $userId) {
            return null;
        }

        // If setting as default, unset others
        if (!empty($data['is_default'])) {
            $this->unsetDefaultAddresses($userId);
        }

        $fields = [];
        $params = [':address_id' => $addressId];

        $allowedFields = ['label', 'full_name', 'phone', 'wilaya', 'commune', 'address_line', 'is_default'];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (!empty($fields)) {
            $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE address_id = :address_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
        }

        return $this->findById($addressId);
    }

    // Delete address
    public function delete($addressId, $userId) {
        $query = "DELETE FROM {$this->table} WHERE address_id = :address_id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':address_id' => $addressId, ':user_id' => $userId]);
    }

    // Unset default addresses
    private function unsetDefaultAddresses($userId) {
        $query = "UPDATE {$this->table} SET is_default = 0 WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $userId]);
    }
}
