<?php

return [
    'mode'     => config('services.paypal.mode', env('PAYPAL_MODE', 'sandbox')),
    'sandbox'  => [
        'client_id'     => config('services.paypal.client_id', env('PAYPAL_SANDBOX_CLIENT_ID', '')),
        'client_secret' => config('services.paypal.secret', env('PAYPAL_SANDBOX_CLIENT_SECRET', '')),
        'app_id'        => 'APP-80W284485P519543T',
    ],
    'live'     => [
        'client_id'     => config('services.paypal.client_id', env('PAYPAL_LIVE_CLIENT_ID', '')),
        'client_secret' => config('services.paypal.secret', env('PAYPAL_LIVE_CLIENT_SECRET', '')),
        'app_id'        => config('services.paypal.app_id', env('PAYPAL_LIVE_APP_ID', '')),
    ],

    'payment_action' => 'Sale',
    'currency'       => 'EUR',
    'locale'         => 'it_IT',
    'validate_ssl'   => true,
    'timeout'        => 30,
    'connect_timeout' => 10,
    'max_retries'    => 2,
];
