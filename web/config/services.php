<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
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

    'payment' => [
        'default' => env('PAYMENT_GATEWAY', 'wave'),
    ],

    'wave' => [
        'api_key' => env('WAVE_API_KEY'),
        'webhook_secret' => env('WAVE_WEBHOOK_SECRET'),
        'base_url' => env('WAVE_BASE_URL', 'https://api.wave.com'),
        // Optionnel : Wave ne publie pas officiellement sa liste d'IPs sortantes.
        // Si fournie (liste séparée par virgules), le webhook rejette toute IP
        // hors whitelist. Laisser vide en pratique — la signature HMAC SHA-256
        // sur Wave-Signature reste la défense primaire.
        'webhook_allowed_ips' => array_filter(explode(',', (string) env('WAVE_WEBHOOK_IPS', ''))),
    ],

    'paystack' => [
        'public' => env('PAYSTACK_PUBLIC_KEY'),
        'secret' => env('PAYSTACK_SECRET_KEY'),
        'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
        'webhook_allowed_ips' => array_filter(explode(',', env('PAYSTACK_WEBHOOK_IPS', '52.31.139.75,52.49.173.169,52.214.14.220'))),
    ],

];
