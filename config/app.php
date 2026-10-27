<?php

return [
    'name' => 'AURA Atelier | Tienda de Ropa en Línea',
    'url' => getenv('APP_URL') ?: 'http://localhost:8000',
    'env' => getenv('APP_ENV') ?: 'development',
    'currency' => 'MXN',
    'currency_symbol' => '$',
    'payment_gateway' => getenv('PAYMENT_GATEWAY') ?: 'simulated', // 'simulated' or 'stripe'
    'stripe_key' => getenv('STRIPE_KEY') ?: 'pk_test_sample',
    'stripe_secret' => getenv('STRIPE_SECRET') ?: 'sk_test_sample',
];
