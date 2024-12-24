<?php
declare(strict_types=1);

/**
 * For external services
 */

return [

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'plaid' => [
        'env' => env('PLAID_ENV', 'sandbox'),
        'secret_key' => env('PLAID_PRODUCTION_SECRET', ''),
        'client_id' => env('PLAID_CLIENT_ID', ''),
        'client_name' => env('APP_NAME'),
        'language' => env('PLAID_LANGUAGE', 'en'),
        'country_codes' => explode(',', env('PLAID_COUNTRY_CODES', 'US')),
        'products' => ['transactions'],
    ],

    'laravelpassport' => [
        'client_id' => env('LARAVEL_PASSPORT_CLIENT_ID'),
        'client_secret' => env('LARAVEL_PASSPORT_CLIENT_SECRET'),
        'redirect' => env('LARAVEL_PASSPORT_REDIRECT'),
        'host' => env('LARAVEL_PASSPORT_HOST')
    ],

    'matrix' => [
        'url' => env('MATRIX_HOST'),
    ]
];
