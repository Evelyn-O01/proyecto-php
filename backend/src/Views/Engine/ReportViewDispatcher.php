<?php

namespace App\Backend\Views\Engine;

use App\Backend\Controllers\ReportController;

class ReportViewDispatcher
{
    private ReportController $controller;

    public function __construct()
    {
        $this->controller = new ReportController();
    }

    public function sales(): void
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $catId = !empty($_GET['category_id']) ? (int)$_GET['category_id'] : null;

        $data = $this->controller->getSalesReport($startDate, $endDate, $catId);
        $data['title'] = 'Reporte Ejecutivo de Ventas e Ingresos | AURA Atelier';
        $data['user'] = ['name' => 'Auditoría Administrativa', 'role' => 'ADMIN'];

        if (($_GET['export'] ?? '') === 'csv') {
            $headers = ['No. Pedido', 'Fecha', 'Cliente', 'Prendas', 'Total MXN'];
            $rows = [];
            foreach ($data['recent_orders'] as $ord) {
                $rows[] = [
                    $ord['order_number'],
                    $ord['created_at'],
                    $ord['customer_name'],
                    $ord['item_count'],
                    number_format((float)$ord['total_amount'], 2, '.', '')
                ];
            }
            ViewRenderer::exportCsv($headers, $rows, 'reporte_ventas_aura_' . date('Ymd_His') . '.csv');
        }

        header('Content-Type: text/html; charset=utf-8');
        echo ViewRenderer::render('Reports/sales_report', $data);
    }

    public function inventory(): void
    {
        $catId = !empty($_GET['category_id']) ? (int)$_GET['category_id'] : null;
        $threshold = max(1, (int)($_GET['threshold'] ?? 10));

        $data = $this->controller->getInventoryReport($catId, $threshold);
        $data['title'] = 'Reporte de Inventario y Stock Crítico | AURA Atelier';
        $data['user'] = ['name' => 'Auditoría Almacén', 'role' => 'ADMIN'];

        if (($_GET['export'] ?? '') === 'csv') {
            $headers = ['Prenda', 'Categoría', 'Talla', 'Color', 'SKU', 'Stock', 'Precio Unitario MXN', 'Valor Total MXN'];
            $rows = [];
            foreach ($data['full_inventory'] as $item) {
                $rows[] = [
                    $item['product_name'],
                    $item['category_name'],
                    $item['size'],
                    $item['color'],
                    $item['sku'],
                    $item['stock'],
                    number_format((float)$item['final_price'], 2, '.', ''),
                    number_format((float)$item['total_value'], 2, '.', '')
                ];
            }
            ViewRenderer::exportCsv($headers, $rows, 'reporte_inventario_aura_' . date('Ymd_His') . '.csv');
        }

        header('Content-Type: text/html; charset=utf-8');
        echo ViewRenderer::render('Reports/inventory_report', $data);
    }

    public function sellers(): void
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $data = $this->controller->getSellerReport($startDate, $endDate);
        $data['title'] = 'Reporte de Desempeño por Vendedor | AURA Atelier';
        $data['user'] = ['name' => 'Auditoría Marketplace', 'role' => 'ADMIN'];

        header('Content-Type: text/html; charset=utf-8');
        echo ViewRenderer::render('Reports/seller_report', $data);
    }
}
