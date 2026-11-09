<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/env.php';

return [
    'driver' => 'pgsql',
    'host' => env_value('DB_HOST', '127.0.0.1'),
    'port' => env_value('DB_PORT', '5432'),
    'database' => env_value('DB_NAME', 'tienda_ropa_db'),
    'username' => env_value('DB_USER', 'postgres'),
    'password' => env_value('DB_PASS', ''),
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
