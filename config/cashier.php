<?php

return [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook' => [
        'secret' => env('STRIPE_WEBHOOK_SECRET'),
        'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
    ],
    'model' => App\Models\User::class,
    'currency' => env('CASHIER_CURRENCY', 'eur'),
    'currency_locale' => env('CASHIER_CURRENCY_LOCALE', 'it_IT'),
    'payment_method_types' => ['card'],

    // Price IDs
    'price_starter' => env('STRIPE_PRICE_STARTER'),
    'price_pro' => env('STRIPE_PRICE_PRO'),
    'price_business' => env('STRIPE_PRICE_BUSINESS'),
];
