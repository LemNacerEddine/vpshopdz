<?php
/**
 * User Model
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/helpers.php';

class User {
    private $conn;
    private $table = 'users';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Find user by email or phone
    public function findByIdentifier($identifier) {
        $query = "SELECT * FROM {$this->table} WHERE email = :email OR phone = :phone LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':email' => $identifier,
            ':phone' => $identifier
        ]);
        return $stmt->fetch();
    }

    // Find user by ID
    public function findById($userId) {
        $query = "SELECT * FROM {$this->table} WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $userId]);
        $user = $stmt->fetch();
        if ($user) {
            unset($user['password_hash']);
            unset($user['id']);
        }
        return $user;
    }

    // Find user by email
    public function findByEmail($email) {
        $query = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    // Create user
    public function create($data) {
        $userId = generateId('user_');
        
        $query = "INSERT INTO {$this->table} (user_id, email, phone, password_hash, name, role) 
                  VALUES (:user_id, :email, :phone, :password_hash, :name, :role)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':user_id' => $userId,
            ':email' => $data['email'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':password_hash' => hashPassword($data['password']),
            ':name' => $data['name'] ?? null,
            ':role' => $data['role'] ?? 'customer'
        ]);

        return $this->findById($userId);
    }

    // Update user
    public function update($userId, $data) {
        $fields = [];
        $params = [':user_id' => $userId];

        foreach (['name', 'phone', 'email', 'avatar'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($fields)) {
            return $this->findById($userId);
        }

        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return $this->findById($userId);
    }

    // Set reset token
    public function setResetToken($email) {
        $token = generateResetToken();
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $query = "UPDATE {$this->table} SET reset_token = :token, reset_token_expires = :expires WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':token' => $token,
            ':expires' => $expires,
            ':email' => $email
        ]);

        return $token;
    }

    // Verify reset token
    public function verifyResetToken($token) {
        $query = "SELECT * FROM {$this->table} WHERE reset_token = :token AND reset_token_expires > NOW() LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':token' => $token]);
        return $stmt->fetch();
    }

    // Reset password
    public function resetPassword($token, $newPassword) {
        $query = "UPDATE {$this->table} SET password_hash = :password, reset_token = NULL, reset_token_expires = NULL 
                  WHERE reset_token = :token";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':password' => hashPassword($newPassword),
            ':token' => $token
        ]);
    }

    // Get all users (admin)
    public function getAll($limit = 100, $offset = 0) {
        $query = "SELECT user_id, email, phone, name, role, avatar, created_at FROM {$this->table} 
                  ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Count users
    public function count() {
        $query = "SELECT COUNT(*) as count FROM {$this->table}";
        $stmt = $this->conn->query($query);
        return $stmt->fetch()['count'];
    }
}
