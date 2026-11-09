<?php

declare(strict_types=1);

// Servir archivos estáticos directamente en el servidor integrado de PHP
if (php_sapi_name() === 'cli-server') {
    $path = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($path)) {
        return false;
    }
}

// Cargar autoloaders: frontend y raíz.
// El autoload raíz registra App\Backend, necesario para renderizar los
// reportes del backend desde el panel de administración del frontend.
$frontendAutoload = __DIR__ . '/../vendor/autoload.php';
$rootAutoload = __DIR__ . '/../../vendor/autoload.php';

if (file_exists($frontendAutoload)) {
    require_once $frontendAutoload;
}

if (file_exists($rootAutoload)) {
    require_once $rootAutoload;
}

if (!file_exists($frontendAutoload) && !file_exists($rootAutoload)) {
    throw new RuntimeException('No se encontró el autoload de Composer.');
}

use App\Frontend\Controllers\AdminController;
use App\Frontend\Controllers\AuthController;
use App\Frontend\Controllers\CartController;
use App\Frontend\Controllers\CheckoutController;
use App\Frontend\Controllers\SellerController;
use App\Frontend\Controllers\StoreController;
use App\Frontend\Services\Session;

Session::start();

// Router del Frontend
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($uri !== '/' && str_ends_with($uri, '/')) {
    $uri = rtrim($uri, '/');
}
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$routes = [
    'GET' => [
        '#^/$#' => fn() => header('Location: /catalog'),
        '#^/catalog$#' => [StoreController::class, 'catalog'],
        '#^/product/(?P<slug>[^/]+)$#' => [StoreController::class, 'detail'],
        '#^/cart$#' => [CartController::class, 'index'],
        '#^/cart/remove/(?P<id>[^/]+)$#' => [CartController::class, 'remove'],
        '#^/checkout$#' => [CheckoutController::class, 'index'],
        '#^/order/success/(?P<number>[^/]+)$#' => [CheckoutController::class, 'success'],
        '#^/orders$#' => [CheckoutController::class, 'myOrders'],
        '#^/login$#' => [AuthController::class, 'showLogin'],
        '#^/auth/quick/(?P<role>[^/]+)$#' => [AuthController::class, 'quickLogin'],
        '#^/register$#' => [AuthController::class, 'showRegister'],
        '#^/logout$#' => [AuthController::class, 'logout'],
        '#^/seller$#' => [SellerController::class, 'dashboard'],
        '#^/seller/product/new$#' => [SellerController::class, 'create'],
        '#^/seller/product/edit/(?P<id>[^/]+)$#' => [SellerController::class, 'edit'],
        '#^/seller/sales$#' => [SellerController::class, 'sales'],
        '#^/admin$#' => [AdminController::class, 'dashboard'],
        '#^/admin/users$#' => [AdminController::class, 'users'],
        '#^/admin/categories$#' => [AdminController::class, 'categories'],
        '#^/admin/reports/sales$#' => [AdminController::class, 'salesReport'],
        '#^/admin/reports/inventory$#' => [AdminController::class, 'inventoryReport'],
        '#^/admin/reports/sellers$#' => [AdminController::class, 'sellersReport'],
    ],
    'POST' => [
        '#^/cart/add$#' => [CartController::class, 'add'],
        '#^/cart/update$#' => [CartController::class, 'update'],
        '#^/checkout/process$#' => [CheckoutController::class, 'process'],
        '#^/login$#' => [AuthController::class, 'login'],
        '#^/register$#' => [AuthController::class, 'register'],
        '#^/seller/product/new$#' => [SellerController::class, 'store'],
        '#^/seller/product/edit/(?P<id>[^/]+)$#' => [SellerController::class, 'update'],
        '#^/seller/product/delete/(?P<id>[^/]+)$#' => [SellerController::class, 'delete'],
        '#^/admin/user/role$#' => [AdminController::class, 'updateRole'],
        '#^/admin/category/new$#' => [AdminController::class, 'createCategory'],
    ]
];

$matched = false;
foreach ($routes[$method] ?? [] as $pattern => $handler) {
    if (preg_match($pattern, $uri, $matches)) {
        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        if (is_array($handler)) {
            [$class, $act] = $handler;
            (new $class())->$act($params);
        } elseif (is_callable($handler)) {
            $handler($params);
        }
        $matched = true;
        break;
    }
}

if (!$matched) {
    http_response_code(404);
    echo StoreController::render('store/404', ['title' => 'Página no encontrada']);
}
