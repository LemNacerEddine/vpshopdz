<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Platform Configuration
    |--------------------------------------------------------------------------
    */

    // Platform domain (used for subdomain resolution)
    'domain' => env('PLATFORM_DOMAIN', 'vpshopdz.com'),

    // Registration
    'registration_enabled' => env('REGISTRATION_ENABLED', true),
    'auto_approve_stores' => env('AUTO_APPROVE_STORES', true),

    // Default plan for new stores
    'default_plan_id' => env('DEFAULT_PLAN_ID', null),

    // Trial
    'trial_days' => env('TRIAL_DAYS', 14),

    // Storage
    'max_file_size_mb' => env('MAX_FILE_SIZE_MB', 10),
    'allowed_file_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'mp4', 'pdf'],

    // Notifications
    'whatsapp_api_url' => env('WHATSAPP_API_URL'),
    'whatsapp_api_token' => env('WHATSAPP_API_TOKEN'),
    'telegram_bot_token' => env('TELEGRAM_BOT_TOKEN'),

    // Facebook Marketing API
    'facebook_app_id' => env('FACEBOOK_APP_ID'),
    'facebook_app_secret' => env('FACEBOOK_APP_SECRET'),

    // Supported languages
    'languages' => ['ar', 'fr', 'en'],
    'default_language' => 'ar',

    // Currency
    'default_currency' => 'DZD',
    'supported_currencies' => ['DZD', 'EUR', 'USD'],

    // Abandoned cart recovery
    'abandoned_cart_timeout_minutes' => env('ABANDONED_CART_TIMEOUT', 60),
    'abandoned_cart_max_reminders' => env('ABANDONED_CART_MAX_REMINDERS', 3),
];
