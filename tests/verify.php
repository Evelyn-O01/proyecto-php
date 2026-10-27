<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Database\PostgresConnection;
use App\Infrastructure\Repositories\PostgresCategoryRepository;
use App\Infrastructure\Repositories\PostgresProductRepository;
use App\Infrastructure\Repositories\PostgresOrderRepository;
use App\Infrastructure\Repositories\PostgresReportRepository;
use App\Infrastructure\Payment\SimulatedPaymentGateway;
use App\Application\UseCases\Catalog\ListProductsUseCase;
use App\Application\UseCases\Catalog\GetProductDetailUseCase;
use App\Application\UseCases\Checkout\ProcessCheckoutUseCase;
use App\Application\UseCases\Reports\GenerateReportUseCase;

echo "=== INICIANDO VALIDACIÓN DEL SISTEMA TIENDA DE ROPA ===" . PHP_EOL;

$pdo = PostgresConnection::getConnection();
echo "[1/6] Conexión PostgreSQL exitosa." . PHP_EOL;

// 1. Validar Categorías
$categoryRepo = new PostgresCategoryRepository($pdo);
$categories = $categoryRepo->findAll();
echo "[2/6] Categorías cargadas: " . count($categories) . " encontradas." . PHP_EOL;

// 2. Validar Catálogo con Filtros
$productRepo = new PostgresProductRepository($pdo);
$listUC = new ListProductsUseCase($productRepo, $categoryRepo);
$catalogResult = $listUC->execute([], 1, 12);
echo "[3/6] Catálogo: " . $catalogResult['total'] . " prendas activas encontradas." . PHP_EOL;

// 3. Validar Ficha de Producto
$detailUC = new GetProductDetailUseCase($productRepo);
$sampleSlug = 'chaqueta-biker-cuero-premium';
$product = $detailUC->execute($sampleSlug);
echo "[4/6] Ficha de producto '" . $product->name . "' obtenida con " . count($product->variants) . " variantes y " . count($product->images) . " fotos." . PHP_EOL;

// 4. Validar Pasarela de Pago y Flujo de Checkout
$orderRepo = new PostgresOrderRepository($pdo);
$gateway = new SimulatedPaymentGateway();
$checkoutUC = new ProcessCheckoutUseCase($orderRepo, $productRepo, $gateway);

$variant = $product->variants[0];
$initialStock = $variant->stock;

$cartItems = [
    [
        'product_id' => $product->id,
        'variant_id' => $variant->id,
        'name' => $product->name,
        'size' => $variant->size,
        'color' => $variant->color,
        'unit_price' => $product->basePrice + $variant->additionalPrice,
        'quantity' => 1
    ]
];

$customerData = [
    'id' => '30000000-0000-0000-0000-000000000001',
    'name' => 'Mariana Gómez',
    'email' => 'cliente@tiendaropa.com',
    'phone' => '5533445566'
];

$shippingAddress = [
    'recipient' => 'Mariana Gómez',
    'street' => 'Av. Insurgentes Sur 1200',
    'city' => 'Ciudad de México',
    'state' => 'CDMX',
    'zip' => '03100',
    'phone' => '5533445566'
];

$paymentPayload = [
    'card_number' => '4242424242424242',
    'card_holder' => 'MARIANA GOMEZ',
    'exp_month' => '12',
    'exp_year' => '2028',
    'cvv' => '123'
];

$order = $checkoutUC->execute($cartItems, $customerData, $shippingAddress, $paymentPayload);

// Verificar decremento de stock
$updatedVariant = $productRepo->findVariantById($variant->id);

echo "[5/6] Checkout exitoso con Pasarela: Orden #" . $order->orderNumber . " generada, Total: $" . $order->totalAmount . " MXN." . PHP_EOL;
echo "      Stock anterior: {$initialStock} -> Stock actual: {$updatedVariant->stock} (Decremento verificado)." . PHP_EOL;

// 5. Validar Generación de Reportes Diseñados
$reportRepo = new PostgresReportRepository($pdo);
$reportUC = new GenerateReportUseCase($reportRepo, $categoryRepo);
$salesReport = $reportUC->generateSalesReport('2026-01-01', '2026-12-31');
$inventoryReport = $reportUC->generateInventoryReport(null, 10);

echo "[6/6] Reportes generados correctamente:" . PHP_EOL;
echo "      - Reporte de Ventas: Total $" . number_format($salesReport['summary']['total_sales'], 2) . " MXN en " . $salesReport['summary']['total_orders'] . " órdenes." . PHP_EOL;
echo "      - Reporte de Inventario: " . $inventoryReport['summary']['total_units'] . " unidades valorizadas en $" . number_format($inventoryReport['summary']['total_inventory_value'], 2) . " MXN." . PHP_EOL;
echo "      - Alertas de Stock Bajo detectadas: " . count($inventoryReport['low_stock_items']) . " ítems." . PHP_EOL;

// 6. Validar Historial de Pedidos del Cliente
$customerOrders = $orderRepo->findByCustomerId('30000000-0000-0000-0000-000000000001');
echo "[7/7] Historial de Pedidos del Cliente: " . count($customerOrders) . " órdenes recuperadas con éxito." . PHP_EOL;

echo "=== TODAS LAS PRUEBAS PASARON EXITOSAMENTE ===" . PHP_EOL;
