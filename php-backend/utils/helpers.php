<?php
/**
 * Helper Functions - FIXED VERSION
 * AgroYousfi E-commerce
 */

// Generate unique ID
function generateId($prefix = '') {
    return $prefix . uniqid() . bin2hex(random_bytes(4));
}

// Generate session token
function generateSessionToken() {
    return 'session_' . bin2hex(random_bytes(32));
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Send JSON response
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// Send error response
function errorResponse($message, $statusCode = 400) {
    jsonResponse(['detail' => $message], $statusCode);
}

// Get JSON input
function getJsonInput() {
    $json = file_get_contents('php://input');
    return json_decode($json, true) ?: [];
}

// Get Bearer token from header
function getBearerToken() {
    $headers = getallheaders();
    $auth = isset($headers['Authorization']) ? $headers['Authorization'] : '';

    if (preg_match('/Bearer\s+(\S+)/', $auth, $matches)) {
        return $matches[1];
    }
    return null;
}

// Get session from cookie
function getSessionFromCookie() {
    return isset($_COOKIE['session_token']) ? $_COOKIE['session_token'] : null;
}

// Set session cookie - FIXED VERSION with proper SameSite
function setSessionCookie($token, $expires) {
    // Check if headers already sent
    if (headers_sent($file, $line)) {
        error_log("Cannot set cookie - headers already sent in $file on line $line");
        return false;
    }

    try {
        // Use array syntax for better control over cookie parameters
        $result = setcookie(
            'session_token',
            $token,
            [
                'expires' => $expires,
                'path' => '/',
                'domain' => '',  // Empty for current domain
                'secure' => true,  // HTTPS only
                'httponly' => true,  // Prevent JavaScript access
                'samesite' => 'Lax'  // IMPORTANT: Allows cookie with top-level navigation
            ]
        );

        if (!$result) {
            error_log("Failed to set session cookie");
            return false;
        }

        error_log("Session cookie set successfully for token: " . substr($token, 0, 20) . "...");
        return true;
    } catch (Exception $e) {
        error_log("Cookie error: " . $e->getMessage());
        return false;
    }
}

// Clear session cookie
function clearSessionCookie() {
    if (headers_sent()) {
        return false;
    }

    setcookie(
        'session_token',
        '',
        [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );

    return true;
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Sanitize input
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Generate reset token
function generateResetToken() {
    return bin2hex(random_bytes(32));
}

// Calculate discount price
function calculateDiscountedPrice($price, $discountPercent) {
    return $price * (1 - $discountPercent / 100);
}

// Check if discount is active
function isDiscountActive($discountPercent, $discountStart, $discountEnd) {
    if (!$discountPercent || $discountPercent <= 0) {
        return false;
    }

    $now = new DateTime();

    if ($discountStart && $discountEnd) {
        $start = new DateTime($discountStart);
        $end = new DateTime($discountEnd);
        return $now >= $start && $now <= $end;
    }

    if ($discountStart) {
        $start = new DateTime($discountStart);
        return $now >= $start;
    }

    return true;
}
