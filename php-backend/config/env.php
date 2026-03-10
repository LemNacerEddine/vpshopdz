<?php
/**
 * Environment Variables Loader
 * AgroYousfi E-commerce
 *
 * Loads environment variables from .env file
 */
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        error_log("Warning: .env file not found at: $filePath");
        return false;
    }
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove quotes if present
            $value = trim($value, '"\'');

            // Set environment variable
            if (!getenv($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }

    return true;
}

// Load .env file from parent directory
$envPath = __DIR__ . '/../.env';
loadEnv($envPath);
