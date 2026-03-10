<?php
/**
 * Environment Configuration Example
 * Copy this to config.php and update values
 */


// Application Settings
putenv('APP_ENV=production');
putenv('APP_DEBUG=false');

// CORS Settings (set to true for development)
putenv('CORS_ALLOW_ALL=false');

// Session Settings
define('SESSION_LIFETIME', 7 * 24 * 60 * 60); // 7 days
