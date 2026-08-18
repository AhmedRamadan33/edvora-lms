<?php

return [
    'default_commission' => (float) env('EDVORA_DEFAULT_COMMISSION', 20),
    'currency' => env('EDVORA_CURRENCY', 'EGP'),
    'supported_currencies' => ['EGP', 'USD', 'EUR', 'SAR', 'AED', 'KWD', 'QAR', 'BHD'],
    'supported_locales' => ['en', 'ar'],
    'default_locale' => env('APP_LOCALE', 'en'),

    'bunny' => [
        'library_id' => env('BUNNY_LIBRARY_ID'),
        'api_key' => env('BUNNY_API_KEY'),
        'cdn_hostname' => env('BUNNY_CDN_HOSTNAME'),
        'token_key' => env('BUNNY_TOKEN_KEY'),
        'token_ttl' => (int) env('BUNNY_TOKEN_TTL', 300),
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

    'payments' => [
        // Demo/local-only completion when gateway keys are missing.
        'allow_demo' => (bool) env('EDVORA_ALLOW_DEMO_PAYMENTS', env('APP_ENV') === 'local'),
        // Allow unsigned webhooks only in local/testing when secrets are missing.
        'allow_unsigned_webhooks' => (bool) env('EDVORA_ALLOW_UNSIGNED_WEBHOOKS', env('APP_ENV') === 'local'),
    ],
];
