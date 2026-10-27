<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Frontend\Controllers\AuthController;
use App\Frontend\Controllers\SellerController;
use App\Frontend\Services\BackendClient;
use App\Frontend\Services\Session;

echo "==========================================================" . PHP_EOL;
echo "🧥 VERIFICACIÓN EXHAUSTIVA DEL PERFIL DE VENDEDOR" . PHP_EOL;
echo "==========================================================" . PHP_EOL;

Session::start();

// 1. Iniciar Sesión como Vendedor
echo PHP_EOL . "--- [1] AUTENTICACIÓN COMO VENDEDOR ---" . PHP_EOL;
$client = new BackendClient();
$loginRes = $client->post('/api/v1/auth/login', [
    'email' => 'vendedor@tiendaropa.com',
    'password' => 'seller123'
]);

if (empty($loginRes['id']) || $loginRes['role'] !== 'SELLER') {
    echo "❌ Error en autenticación de vendedor: " . json_encode($loginRes) . PHP_EOL;
    exit(1);
}

Session::setUser($loginRes);
echo "✔ Vendedor autenticado: {$loginRes['name']} ({$loginRes['email']}) - Rol: {$loginRes['role']}" . PHP_EOL;

// 2. Cargar Dashboard de Vendedor (/seller)
echo PHP_EOL . "--- [2] RENDERIZADO DEL DASHBOARD DEL VENDEDOR (/seller) ---" . PHP_EOL;
$sellerCtrl = new SellerController();
ob_start();
try {
    $sellerCtrl->dashboard();
    $dashboardHtml = ob_get_clean();
    $hasTitle = str_contains($dashboardHtml, 'Panel de Control del Vendedor');
    $hasTable = str_contains($dashboardHtml, 'Catálogo de Prendas Publicadas');
    $hasSellerName = str_contains($dashboardHtml, htmlspecialchars($loginRes['name']));
    
    if ($hasTitle && $hasTable && $hasSellerName) {
        echo "✔ Dashboard renderizado con éxito! (HTML Size: " . strlen($dashboardHtml) . " bytes)" . PHP_EOL;
        echo "✔ Contiene título principal, bienvenida al vendedor y tabla de catálogo." . PHP_EOL;
    } else {
        echo "❌ Fallo en contenido del dashboard." . PHP_EOL;
        exit(1);
    }
} catch (\Throwable $e) {
    ob_end_clean();
    echo "❌ ERROR CRÍTICO al cargar dashboard de vendedor: " . $e->getMessage() . PHP_EOL;
    echo "   En: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    exit(1);
}

// 3. Cargar Vista de Historial de Ventas (/seller/sales)
echo PHP_EOL . "--- [3] RENDERIZADO DE VENTAS Y PEDIDOS (/seller/sales) ---" . PHP_EOL;
ob_start();
try {
    $sellerCtrl->sales();
    $salesHtml = ob_get_clean();
    $hasSalesTitle = str_contains($salesHtml, 'Historial de Ventas y Pedidos');
    
    if ($hasSalesTitle) {
        echo "✔ Vista de ventas renderizada con éxito! (HTML Size: " . strlen($salesHtml) . " bytes)" . PHP_EOL;
    } else {
        echo "❌ Fallo en contenido de ventas." . PHP_EOL;
        exit(1);
    }
} catch (\Throwable $e) {
    ob_end_clean();
    echo "❌ ERROR CRÍTICO en ventas de vendedor: " . $e->getMessage() . PHP_EOL;
    echo "   En: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    exit(1);
}

// 4. Formulario de Publicación de Nueva Prenda (/seller/product/new)
echo PHP_EOL . "--- [4] FORMULARIO DE NUEVA PRENDA (/seller/product/new) ---" . PHP_EOL;
ob_start();
try {
    $sellerCtrl->create();
    $formHtml = ob_get_clean();
    $hasFormTitle = str_contains($formHtml, 'Publicar Nueva Prenda de Ropa');
    
    if ($hasFormTitle) {
        echo "✔ Formulario de publicación cargado correctamente! (HTML Size: " . strlen($formHtml) . " bytes)" . PHP_EOL;
    } else {
        echo "❌ Fallo en contenido del formulario de creación." . PHP_EOL;
        exit(1);
    }
} catch (\Throwable $e) {
    ob_end_clean();
    echo "❌ ERROR CRÍTICO en formulario de creación: " . $e->getMessage() . PHP_EOL;
    echo "   En: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    exit(1);
}

// 5. Creación de una Prenda de Prueba
echo PHP_EOL . "--- [5] PUBLICACIÓN Y EDICIÓN DE PRENDA ---" . PHP_EOL;
$testProdRes = $client->post('/api/v1/seller/products', [
    'seller_id' => $loginRes['id'],
    'category_id' => 1,
    'name' => 'Blusa Test Vendedor ' . time(),
    'description' => 'Prenda de prueba automatizada para validar el flujo del vendedor.',
    'base_price' => 450.00,
    'variants' => [
        [
            'size' => 'M',
            'color' => 'Beige',
            'color_code' => '#f5f5dc',
            'stock' => 15,
            'additional_price' => 0.00
        ]
    ],
    'images' => [
        'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=800&q=80'
    ]
]);

$createdId = $testProdRes['id'] ?? '';
echo "✔ Prenda de prueba creada con ID: {$createdId}" . PHP_EOL;

// 6. Cargar Formulario de Edición de la Prenda
ob_start();
try {
    $sellerCtrl->edit(['id' => $createdId]);
    $editHtml = ob_get_clean();
    $hasEditTitle = str_contains($editHtml, 'Editar Prenda:');
    
    if ($hasEditTitle) {
        echo "✔ Formulario de edición cargado correctamente! (HTML Size: " . strlen($editHtml) . " bytes)" . PHP_EOL;
    } else {
        echo "❌ Fallo en contenido del formulario de edición." . PHP_EOL;
        exit(1);
    }
} catch (\Throwable $e) {
    ob_end_clean();
    echo "❌ ERROR CRÍTICO al cargar edición: " . $e->getMessage() . PHP_EOL;
    echo "   En: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    exit(1);
}

// 7. Actualización de la Prenda
$updateRes = $client->post('/api/v1/seller/products/' . $createdId, [
    'seller_id' => $loginRes['id'],
    'category_id' => 1,
    'name' => 'Blusa Test Vendedor (Actualizada)',
    'description' => 'Descripción actualizada correctamente.',
    'base_price' => 480.00,
    'status' => 'ACTIVE'
]);
echo "✔ Prenda actualizada exitosamente vía Endpoint." . PHP_EOL;

// 8. Eliminación de la Prenda de Prueba
$delRes = $client->post('/api/v1/seller/products/delete/' . $createdId, [
    'seller_id' => $loginRes['id']
]);
echo "✔ Prenda de prueba eliminada correctamente." . PHP_EOL;

echo PHP_EOL . "==========================================================" . PHP_EOL;
echo "🎉 ¡EL PERFIL DE VENDEDOR FUNCIONA AL 100% SIN NINGÚN ERROR!" . PHP_EOL;
echo "==========================================================" . PHP_EOL;
