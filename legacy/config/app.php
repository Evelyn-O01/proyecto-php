<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';

return [
    'name' => env_value('APP_NAME', 'AURA Atelier | Tienda de Ropa en Línea'),
    'url' => env_value('APP_URL', 'http://localhost:8000'),
    'env' => env_value('APP_ENV', 'development'),
    'currency' => 'MXN',
    'currency_symbol' => '$',
    'payment_gateway' => env_value('PAYMENT_GATEWAY', 'simulated'), // 'simulated' or 'stripe'
    'stripe_key' => env_value('STRIPE_KEY', 'pk_test_sample'),
    'stripe_secret' => env_value('STRIPE_SECRET', 'sk_test_sample'),
];
