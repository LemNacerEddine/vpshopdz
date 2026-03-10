<?php
/**
 * Google OAuth Helper Functions
 * AgroYousfi E-commerce
 *
 * Separate file to avoid conflicts with Auth class
 */

/**
 * Create session for Google OAuth
 * @param PDO $db Database connection
 * @param string $userId User ID
 * @return string|null Session ID on success, null on failure
 */
function createGoogleSession($db, $userId) {
    try {
        // Generate session ID
        $sessionId = 'sess_' . bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

        // Check if sessions table exists
        $checkTable = "SHOW TABLES LIKE 'sessions'";
        $result = $db->query($checkTable);

        if ($result->rowCount() == 0) {
            // Create sessions table
            $createTable = "CREATE TABLE IF NOT EXISTS sessions (
                session_id VARCHAR(100) PRIMARY KEY,
                user_id VARCHAR(50) NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_expires_at (expires_at)
            )";
            $db->exec($createTable);
            error_log("Sessions table created successfully");
        }

        // Insert session
        $stmt = $db->prepare("
            INSERT INTO sessions (session_id, user_id, expires_at) 
            VALUES (?, ?, ?)
        ");

        $success = $stmt->execute([$sessionId, $userId, $expiresAt]);

        if ($success) {
            error_log("Google session created successfully for user: $userId");
            return $sessionId;
        }

        error_log("Failed to create Google session for user: $userId");
        return null;
    } catch (Exception $e) {
        error_log("createGoogleSession error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return null;
    }
}

/**
 * Set session cookie for Google OAuth - FIXED VERSION
 * @param string $sessionId Session ID
 * @param int $expires Expiration timestamp
 */
function setGoogleSessionCookie($sessionId, $expires) {
    try {
        $cookieSet = setcookie(
            'session_token',
            $sessionId,
            [
                'expires' => $expires,
                'path' => '/',
                'domain' => '',  // Empty for current domain
                'secure' => true,  // HTTPS only
                'httponly' => true,  // Prevent JavaScript access
                'samesite' => 'Lax'  // IMPORTANT: Allows cookie with top-level navigation
            ]
        );

        if ($cookieSet) {
            error_log("Google session cookie set successfully for session: " . substr($sessionId, 0, 20) . "...");
        } else {
            error_log("Failed to set Google session cookie");
        }
    } catch (Exception $e) {
        error_log("setGoogleSessionCookie error: " . $e->getMessage());
    }
}
