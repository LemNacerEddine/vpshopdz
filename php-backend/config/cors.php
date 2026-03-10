<?php
/**
 * CORS Configuration
 * AgroYousfi E-commerce
 * Updated for Google OAuth support
 *
 * This file handles Cross-Origin Resource Sharing (CORS) headers
 * to allow frontend (React) to communicate with backend (PHP API)
 */

// Define allowed origins
$allowed_origins = [
    'https://vpdeveloper.dz',           // Production domain
    'http://localhost:3000',            // Development (React dev server)
    'http://localhost:5173',            // Development (Vite dev server)
    'http://127.0.0.1:3000',           // Development (alternative localhost)
    'http://127.0.0.1:5173'            // Development (alternative localhost)
];

// Get the origin from the request
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// Check if the origin is in the allowed list
if (in_array($origin, $allowed_origins)) {
    // Allow the specific origin
    header("Access-Control-Allow-Origin: $origin");

    // Allow credentials (cookies, authorization headers)
    // This is REQUIRED for session-based authentication and Google OAuth
    header("Access-Control-Allow-Credentials: true");
} else {
    // For security, reject unknown origins in production
    // Log the unauthorized attempt for monitoring
    if ($origin) {
        error_log("CORS: Unauthorized origin attempted access: $origin");
    }

    // Uncomment the following lines ONLY for development/testing
    // if you need to allow all origins temporarily
    // header("Access-Control-Allow-Origin: $origin");
    // header("Access-Control-Allow-Credentials: true");
}

// Specify allowed HTTP methods
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Specify allowed headers
// Add any custom headers your frontend sends here
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Cache preflight response for 24 hours (86400 seconds)
// This reduces the number of OPTIONS requests and improves performance
header("Access-Control-Max-Age: 86400");

// Set content type for JSON responses
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS requests
// Browsers send OPTIONS request before actual request to check CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Return 200 OK for preflight
    http_response_code(200);
    exit();
}

/**
 * IMPORTANT NOTES:
 *
 * 1. Access-Control-Allow-Origin CANNOT be "*" when using credentials
 *    - Must specify exact origin
 *    - Required for cookies and sessions
 *
 * 2. Access-Control-Allow-Credentials must be "true" for:
 *    - Session cookies
 *    - Authentication tokens in cookies
 *    - Google OAuth callback with session
 *
 * 3. For production, only include production domain in $allowed_origins
 *    - Remove localhost entries
 *    - Keep only: 'https://vpdeveloper.dz'
 *
 * 4. Access-Control-Max-Age caches preflight for 24 hours
 *    - Reduces OPTIONS requests
 *    - Improves performance
 *    - Browser will cache CORS policy
 *
 * 5. If you add custom headers in frontend (axios), add them to:
 *    - Access-Control-Allow-Headers
 *
 * 6. Security:
 *    - Never use "*" with credentials
 *    - Always validate origin
 *    - Log unauthorized attempts
 *    - Review logs regularly
 */
