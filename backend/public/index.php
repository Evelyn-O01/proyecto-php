<?php

declare(strict_types=1);

// Servir archivos estáticos directamente en el servidor integrado de PHP
if (php_sapi_name() === 'cli-server') {
    $path = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($path)) {
        return false;
    }
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\Backend\Endpoints\AdminEndpoints;
use App\Backend\Endpoints\ApiRouter;
use App\Backend\Endpoints\AuthEndpoints;
use App\Backend\Endpoints\CatalogEndpoints;
use App\Backend\Endpoints\CheckoutEndpoints;
use App\Backend\Endpoints\ReportEndpoints;
use App\Backend\Endpoints\SellerEndpoints;
use App\Backend\Views\Engine\ReportViewDispatcher;

$router = new ApiRouter();

// ----------------------------------------------------
// 🌐 1. ENDPOINTS: API RESTful (Salida JSON)
// ----------------------------------------------------

// Catálogo & Productos
$router->get('/api/v1/products', [CatalogEndpoints::class, 'index']);
$router->get('/api/v1/products/{slug}', [CatalogEndpoints::class, 'show']);
$router->get('/api/v1/categories', [CatalogEndpoints::class, 'categories']);

// Autenticación & Perfiles
$router->post('/api/v1/auth/login', [AuthEndpoints::class, 'login']);
$router->post('/api/v1/auth/register', [AuthEndpoints::class, 'register']);
$router->get('/api/v1/auth/user/{id}', [AuthEndpoints::class, 'getUser']);

// Checkout & Órdenes
$router->post('/api/v1/checkout/process', [CheckoutEndpoints::class, 'process']);
$router->get('/api/v1/orders/{number}', [CheckoutEndpoints::class, 'getOrder']);
$router->get('/api/v1/customer/{id}/orders', [CheckoutEndpoints::class, 'getCustomerOrders']);

// Portal Vendedores
$router->get('/api/v1/seller/{id}/products', [SellerEndpoints::class, 'products']);
$router->post('/api/v1/seller/products', [SellerEndpoints::class, 'create']);
$router->post('/api/v1/seller/products/{id}', [SellerEndpoints::class, 'update']);
$router->post('/api/v1/seller/products/delete/{id}', [SellerEndpoints::class, 'delete']);
$router->get('/api/v1/seller/{id}/sales', [SellerEndpoints::class, 'sales']);

// Administración & RBAC
$router->get('/api/v1/admin/users', [AdminEndpoints::class, 'users']);
$router->post('/api/v1/admin/users/role', [AdminEndpoints::class, 'updateRole']);
$router->post('/api/v1/admin/categories', [AdminEndpoints::class, 'createCategory']);

// Datos Crudos para Reportes (JSON)
$router->get('/api/v1/reports/sales/data', [ReportEndpoints::class, 'sales']);
$router->get('/api/v1/reports/inventory/data', [ReportEndpoints::class, 'inventory']);
$router->get('/api/v1/reports/sellers/data', [ReportEndpoints::class, 'sellers']);

// ----------------------------------------------------
// 📄 2. VISTAS DEL BACKEND: Motor de Reportes Diseñados (HTML/Print/PDF/CSV)
// ----------------------------------------------------
$router->get('/reports/sales', [ReportViewDispatcher::class, 'sales']);
$router->get('/reports/inventory', [ReportViewDispatcher::class, 'inventory']);
$router->get('/reports/sellers', [ReportViewDispatcher::class, 'sellers']);

// Despachar Petición
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';

if (!$router->dispatch($method, $uri)) {
    if (str_starts_with($uri, '/api/')) {
        ApiRouter::json(['success' => false, 'error' => 'Endpoint no encontrado'], 404);
    } else {
        header('Location: /reports/sales');
        exit;
    }
}
