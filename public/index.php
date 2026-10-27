<?php

declare(strict_types=1);

// Servir archivos estáticos directamente si se usa el servidor integrado de PHP
if (php_sapi_name() === 'cli-server') {
    $path = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($path)) {
        return false;
    }
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\Presentation\Controllers\AdminController;
use App\Presentation\Controllers\AuthController;
use App\Presentation\Controllers\CartController;
use App\Presentation\Controllers\CatalogController;
use App\Presentation\Controllers\CheckoutController;
use App\Presentation\Controllers\ProductController;
use App\Presentation\Controllers\ReportController;
use App\Presentation\Controllers\SellerController;
use App\Presentation\Middlewares\SessionManager;
use App\Presentation\Router;

// Inicializar sesión de usuario
SessionManager::start();

// Enrutador HTTP
$router = new Router();

// Redirección de Inicio a Catálogo
$router->get('/', function() {
    header('Location: /catalog');
    exit;
});

// Catálogo y Ficha de Productos
$router->get('/catalog', [CatalogController::class, 'index']);
$router->get('/product/{slug}', [ProductController::class, 'show']);

// Carrito de Compras
$router->get('/cart', [CartController::class, 'index']);
$router->post('/cart/add', [CartController::class, 'add']);
$router->post('/cart/update', [CartController::class, 'update']);
$router->get('/cart/remove/{id}', [CartController::class, 'remove']);

// Checkout y Pasarela de Pago
$router->get('/checkout', [CheckoutController::class, 'index']);
$router->post('/checkout/process', [CheckoutController::class, 'process']);
$router->get('/order/success/{number}', [CheckoutController::class, 'success']);
$router->get('/orders', [CheckoutController::class, 'myOrders']);

// Autenticación & RBAC
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/auth/quick/{role}', [AuthController::class, 'quickLogin']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);

// Panel de Vendedor
$router->get('/seller', [SellerController::class, 'index']);
$router->get('/seller/product/new', [SellerController::class, 'create']);
$router->post('/seller/product/new', [SellerController::class, 'store']);
$router->get('/seller/product/edit/{id}', [SellerController::class, 'edit']);
$router->post('/seller/product/edit/{id}', [SellerController::class, 'update']);
$router->post('/seller/product/delete/{id}', [SellerController::class, 'delete']);
$router->get('/seller/sales', [SellerController::class, 'sales']);

// Panel de Administración y Reportes Ejecutivos
$router->get('/admin', [AdminController::class, 'index']);
$router->get('/admin/users', [AdminController::class, 'users']);
$router->post('/admin/user/role', [AdminController::class, 'updateRole']);
$router->get('/admin/categories', [AdminController::class, 'categories']);
$router->post('/admin/category/new', [AdminController::class, 'createCategory']);
$router->get('/admin/reports/sales', [ReportController::class, 'sales']);
$router->get('/admin/reports/inventory', [ReportController::class, 'inventory']);
$router->get('/admin/reports/sellers', [ReportController::class, 'sellers']);

// Despachar Petición
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';

$router->dispatch($method, $uri);
