<?php

return [
    'default_commission' => (float) env('EDVORA_DEFAULT_COMMISSION', 20),
    'currency' => env('EDVORA_CURRENCY', 'EGP'),
    'supported_currencies' => ['EGP', 'USD', 'EUR', 'SAR', 'AED', 'KWD', 'QAR', 'BHD'],
    'supported_locales' => ['en', 'ar'],
    'default_locale' => env('APP_LOCALE', 'en'),

    'vdocipher' => [
        'api_secret' => env('VDOCIPHER_API_SECRET'),
        'webhook_token' => env('VDOCIPHER_WEBHOOK_TOKEN'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'paymob' => [
        'api_key' => env('PAYMOB_API_KEY'),
        'integration_id' => env('PAYMOB_INTEGRATION_ID'),
        'iframe_id' => env('PAYMOB_IFRAME_ID'),
        'hmac_secret' => env('PAYMOB_HMAC_SECRET'),
        // Kept for reference only. Runtime Paymob charges use the platform currency.
        'currency' => env('PAYMOB_CURRENCY', env('EDVORA_CURRENCY', 'EGP')),
    ],

    'paytabs' => [
        'profile_id' => env('PAYTABS_PROFILE_ID'),
        'server_key' => env('PAYTABS_SERVER_KEY'),
        'region' => env('PAYTABS_REGION', 'egypt'),
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret' => env('PAYPAL_SECRET'),
        'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
        'mode' => env('PAYPAL_MODE', 'sandbox'),
    ],

    'fawry' => [
        'merchant_code' => env('FAWRY_MERCHANT_CODE'),
        'security_key' => env('FAWRY_SECURITY_KEY'),
        'mode' => env('FAWRY_MODE', 'sandbox'), // sandbox | live
    ],

    'display_timezone' => env('EDVORA_DISPLAY_TIMEZONE', 'Africa/Cairo'),

    'zoom' => [
        'client_id' => env('ZOOM_CLIENT_ID'),
        'client_secret' => env('ZOOM_CLIENT_SECRET'),
    ],

    'google_meet' => [
        'client_id' => env('GOOGLE_MEET_CLIENT_ID'),
        'client_secret' => env('GOOGLE_MEET_CLIENT_SECRET'),
    ],

    'payments' => [
        // Demo/local-only completion when gateway keys are missing.
        'allow_demo' => (bool) env('EDVORA_ALLOW_DEMO_PAYMENTS', env('APP_ENV') === 'local'),
        // Allow unsigned webhooks only in local/testing when secrets are missing.
        'allow_unsigned_webhooks' => (bool) env('EDVORA_ALLOW_UNSIGNED_WEBHOOKS', env('APP_ENV') === 'local'),
    ],
];
