<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/env.php';

$backendHost = env_value('BACKEND_HOST', '127.0.0.1');
$backendPort = env_value('BACKEND_PORT', '8080');
$defaultBackendUrl = 'http://' . $backendHost . ':' . $backendPort;

return [
    'base_url' => env_value('BACKEND_API_URL', $defaultBackendUrl),
    'reports_url' => env_value('BACKEND_REPORTS_URL', $defaultBackendUrl . '/reports'),
    'timeout' => (int) env_value('BACKEND_TIMEOUT', '10'),
];
