<?php
/**
 * Authentication Middleware - FIXED VERSION
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/helpers.php';

class Auth {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    // Get current user from session
    public function getCurrentUser() {
        $token = getBearerToken() ?: getSessionFromCookie();

        if (!$token) {
            return null;
        }

        $query = "SELECT u.* FROM users u 
                  JOIN sessions s ON u.user_id = s.user_id 
                  WHERE s.session_id = :token AND s.expires_at > NOW()";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        $user = $stmt->fetch();

        if ($user) {
            unset($user['password_hash']);
            unset($user['id']);
        }

        return $user;
    }

    // Require authentication
    public function requireAuth() {
        $user = $this->getCurrentUser();
        if (!$user) {
            errorResponse('غير مصرح به', 401);
        }
        return $user;
    }

    // Require admin role
    public function requireAdmin() {
        $user = $this->requireAuth();
        if ($user['role'] !== 'admin') {
            errorResponse('صلاحيات غير كافية', 403);
        }
        return $user;
    }

    // Create session - FIXED VERSION
    public function createSession($userId) {
        try {
            $sessionId = generateSessionToken();
            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

            // Check if sessions table exists
            $checkTable = "SHOW TABLES LIKE 'sessions'";
            $result = $this->conn->query($checkTable);

            if ($result->rowCount() == 0) {
                // Create sessions table if it doesn't exist
                $createTable = "CREATE TABLE IF NOT EXISTS sessions (
                    session_id VARCHAR(100) PRIMARY KEY,
                    user_id VARCHAR(50) NOT NULL,
                    expires_at DATETIME NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
                )";
                $this->conn->exec($createTable);
            }

            // Insert session
            $query = "INSERT INTO sessions (session_id, user_id, expires_at) 
                     VALUES (:session_id, :user_id, :expires_at)";
            $stmt = $this->conn->prepare($query);
            $success = $stmt->execute([
                ':session_id' => $sessionId,
                ':user_id' => $userId,
                ':expires_at' => $expiresAt
            ]);

            if (!$success) {
                throw new Exception('Failed to create session in database');
            }

            // Set cookie - 30 days
            $expires = time() + (30 * 24 * 60 * 60);
            setSessionCookie($sessionId, $expires);

            return $sessionId;
        } catch (Exception $e) {
            error_log('Session creation error: ' . $e->getMessage());
            throw new Exception('فشل إنشاء الجلسة: ' . $e->getMessage());
        }
    }

    // Destroy session
    public function destroySession() {
        $token = getBearerToken() ?: getSessionFromCookie();

        if ($token) {
            $query = "DELETE FROM sessions WHERE session_id = :token";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':token' => $token]);
        }

        clearSessionCookie();
    }

    // Clean expired sessions
    public function cleanExpiredSessions() {
        $query = "DELETE FROM sessions WHERE expires_at < NOW()";
        $this->conn->exec($query);
    }
}
