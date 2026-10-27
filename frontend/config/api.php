<?php

return [
    'base_url' => getenv('BACKEND_API_URL') ?: 'http://127.0.0.1:8080',
    'reports_url' => getenv('BACKEND_REPORTS_URL') ?: 'http://127.0.0.1:8080/reports',
    'timeout' => 10,
];
