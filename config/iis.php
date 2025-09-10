<?php

return [
    /*
    |--------------------------------------------------------------------------
    | IIS Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration specific to IIS deployment
    |
    */

    'url_rewrite' => [
        'enabled' => true,
        'base_path' => env('IIS_BASE_PATH', ''),
    ],

    'ajax' => [
        'base_url' => env('AJAX_BASE_URL', ''),
        'timeout' => 30000,
        'retry_attempts' => 3,
    ],

    'headers' => [
        'cors_enabled' => true,
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'X-CSRF-TOKEN'],
    ],
];
