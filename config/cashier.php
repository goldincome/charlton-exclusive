<?php

return [

    'key' => env('STRIPE_KEY'),

    'secret' => env('STRIPE_SECRET'),

    'webhook' => [
        'endpoint' => env('STRIPE_WEBHOOK_ENDPOINT', '/stripe/webhook'),
        'secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'currency' => env('CASHIER_CURRENCY', 'gbp'),

    'locale' => env('CASHIER_LOCALE', 'auto'),

    'currency_locale' => env('CASHIER_CURRENCY_LOCALE', 'en_GB'),

    'options' => [
        'stripe' => [
            'host' => env('STRIPE_HOST', 'api.stripe.com'),
        ],
    ],

];
