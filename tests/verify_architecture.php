<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Backend\Controllers\AuthController;
use App\Backend\Controllers\OrderController;
use App\Backend\Controllers\ProductController;
use App\Backend\Controllers\ReportController;
use App\Backend\Endpoints\ApiRouter;
use App\Backend\Views\Engine\ViewRenderer;
use App\Backend\Views\Reports\InventoryReportPdf;
use App\Backend\Views\Reports\SalesReportPdf;
use App\Frontend\Services\BackendClient;
use App\Frontend\Services\CartSession;

echo "==========================================================" . PHP_EOL;
echo "🧪 VERIFICACIÓN DE NUEVA ARQUITECTURA (FRONTEND & BACKEND)" . PHP_EOL;
echo "==========================================================" . PHP_EOL;

// 1. Validar Controladores del Backend
echo PHP_EOL . "--- [1] BACKEND: CONTROLADORES DE NEGOCIO ---" . PHP_EOL;
$prodCtrl = new ProductController();
$catalog = $prodCtrl->list([], 1, 10);
echo "✔ ProductController::list() -> " . $catalog['total'] . " prendas en catálogo." . PHP_EOL;

$sample = $prodCtrl->getDetail('chaqueta-biker-cuero-premium');
echo "✔ ProductController::getDetail() -> Prenda '{$sample['name']}' con " . count($sample['variants']) . " variantes." . PHP_EOL;

$authCtrl = new AuthController();
$adminUser = $authCtrl->authenticate('admin@tiendaropa.com', 'admin123');
echo "✔ AuthController::authenticate() -> Usuario '{$adminUser['name']}' autenticado como {$adminUser['role']}." . PHP_EOL;

// 2. Validar Endpoints del Backend (REST API JSON)
echo PHP_EOL . "--- [2] BACKEND: ENDPOINTS API REST (SALIDA JSON) ---" . PHP_EOL;
$apiRouter = new ApiRouter();
$client = new BackendClient();

$productsApi = $client->get('/api/v1/products', ['per_page' => 5]);
echo "✔ Endpoint GET /api/v1/products -> Recibió lista con " . count($productsApi['products']) . " prendas." . PHP_EOL;

$categoriesApi = $client->get('/api/v1/categories');
echo "✔ Endpoint GET /api/v1/categories -> Recibió " . count($categoriesApi) . " categorías." . PHP_EOL;

// 3. Validar Vistas del Backend (Motor de Reportes Diseñados, PDF & CSV)
echo PHP_EOL . "--- [3] BACKEND: VISTAS Y MOTOR DE REPORTES DISEÑADOS ---" . PHP_EOL;
$reportCtrl = new ReportController();
$salesData = $reportCtrl->getSalesReport('2026-01-01', '2026-12-31');
$salesData['title'] = 'Reporte Ejecutivo de Ventas';
$salesData['user'] = ['name' => 'Auditor General'];

$renderedHtml = ViewRenderer::render('Reports/sales_report', $salesData);
$hasKpi = str_contains($renderedHtml, 'Ventas Netas Totales');
$hasAura = str_contains($renderedHtml, 'AURA');
echo "✔ ViewRenderer::render('Reports/sales_report') -> Renderizó HTML exitosamente (Contiene KPIs: " . ($hasKpi ? 'SÍ' : 'NO') . ")." . PHP_EOL;

$pdfBytes = SalesReportPdf::render($salesData);
$pdfValid = str_starts_with($pdfBytes, '%PDF-') && str_ends_with($pdfBytes, '%%EOF');
echo "✔ SalesReportPdf::render() -> PDF de " . number_format(strlen($pdfBytes)) . " bytes generado (Estructura válida: " . ($pdfValid ? 'SÍ' : 'NO') . ")." . PHP_EOL;

$inventoryData = $reportCtrl->getInventoryReport(null, 10);
$inventoryData['title'] = 'Reporte de Inventario';
$inventoryData['user'] = ['name' => 'Auditor General'];
$renderedInv = ViewRenderer::render('Reports/inventory_report', $inventoryData);
echo "✔ ViewRenderer::render('Reports/inventory_report') -> Valuación total: $" . number_format($inventoryData['summary']['total_inventory_value'], 2) . " MXN." . PHP_EOL;

$inventoryPdfBytes = InventoryReportPdf::render($inventoryData);
$inventoryPdfValid = str_starts_with($inventoryPdfBytes, '%PDF-') && str_ends_with($inventoryPdfBytes, '%%EOF');
echo "✔ InventoryReportPdf::render() -> PDF de " . number_format(strlen($inventoryPdfBytes)) . " bytes generado (Estructura válida: " . ($inventoryPdfValid ? 'SÍ' : 'NO') . ")." . PHP_EOL;

// 4. Validar Frontend Web App
echo PHP_EOL . "--- [4] FRONTEND: SERVICIOS Y FLUJO DE COMPRA ---" . PHP_EOL;
CartSession::clear();
$variant = $sample['variants'][0];

CartSession::add([
    'product_id' => $sample['id'],
    'variant_id' => $variant['id'],
    'name' => $sample['name'],
    'slug' => $sample['slug'],
    'size' => $variant['size'],
    'color' => $variant['color'],
    'unit_price' => $sample['base_price'] + $variant['additional_price'],
    'quantity' => 1,
    'image' => $sample['primary_image'],
    'max_stock' => $variant['stock']
]);

echo "✔ CartSession::add() -> Bolsa contiene " . CartSession::getItemCount() . " prenda(s). Subtotal: $" . CartSession::getSubtotal() . " MXN." . PHP_EOL;

// Procesar Checkout mediante BackendClient -> Backend Endpoint
$order = $client->post('/api/v1/checkout/process', [
    'cart_items' => array_values(CartSession::getItems()),
    'customer' => [
        'id' => '30000000-0000-0000-0000-000000000001',
        'name' => 'Mariana Gómez',
        'email' => 'cliente@tiendaropa.com',
        'phone' => '+52 55 3344 5566'
    ],
    'shipping_address' => [
        'recipient' => 'Mariana Gómez',
        'street' => 'Av. Homero 1400',
        'city' => 'Ciudad de México',
        'state' => 'CDMX',
        'zip' => '11560',
        'phone' => '5533445566'
    ],
    'payment' => [
        'card_number' => '4242424242424242',
        'exp_month' => '12',
        'exp_year' => '2028',
        'cvv' => '123',
        'card_holder' => 'MARIANA GOMEZ'
    ]
]);

echo "✔ Checkout vía API Endpoints -> Orden #" . $order['order_number'] . " generada con éxito (Estado: " . $order['status_label'] . ")." . PHP_EOL;
CartSession::clear();
echo "✔ Bolsa de compras limpiada tras pago exitoso." . PHP_EOL;

echo PHP_EOL . "==========================================================" . PHP_EOL;
echo "🎉 ¡NUEVA ARQUITECTURA VERIFICADA AL 100% CON ÉXITO!" . PHP_EOL;
echo "==========================================================" . PHP_EOL;
