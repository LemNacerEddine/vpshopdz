<?php
/**
 * Authentication Middleware
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

    // Create session
    public function createSession($userId) {
        $sessionId = generateSessionToken();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

        $query = "INSERT INTO sessions (session_id, user_id, expires_at) VALUES (:session_id, :user_id, :expires_at)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':session_id' => $sessionId,
            ':user_id' => $userId,
            ':expires_at' => $expiresAt
        ]);

        setSessionCookie($sessionId, strtotime('+7 days'));
        
        return $sessionId;
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
