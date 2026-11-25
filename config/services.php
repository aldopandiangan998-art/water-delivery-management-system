<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'dana' => [
        'webhook_secret' => env('DANA_WEBHOOK_SECRET', 'dana-novajaya-secret-2026'),
    ],

    'fonnte' => [
        'token' => env('FONNTE_API_TOKEN', ''),
        'endpoint' => env('FONNTE_ENDPOINT', 'https://api.fonnte.com/send'),
    ],

    'tripay' => [
        'api_key' => env('TRIPAY_API_KEY', ''),
        'private_key' => env('TRIPAY_PRIVATE_KEY', ''),
        'merchant_code' => env('TRIPAY_MERCHANT_CODE', ''),
        'mode' => env('TRIPAY_MODE', 'sandbox'), // sandbox or live
        'pass_fee_to_customer' => env('TRIPAY_PASS_FEE_TO_CUSTOMER', true),
        'qris_rate' => env('TRIPAY_QRIS_RATE', 0.007), // 0.7% standard MDR BI
    ],

];
