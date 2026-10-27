<?php

namespace App\Presentation\Controllers;

use App\Application\UseCases\Reports\GenerateReportUseCase;
use App\Infrastructure\Database\PostgresConnection;
use App\Infrastructure\Repositories\PostgresCategoryRepository;
use App\Infrastructure\Repositories\PostgresReportRepository;
use App\Presentation\Middlewares\RoleMiddleware;
use App\Presentation\Middlewares\SessionManager;
use App\Presentation\Router;

class ReportController
{
    private GenerateReportUseCase $reportUseCase;

    public function __construct()
    {
        RoleMiddleware::requireRole('ADMIN');
        $pdo = PostgresConnection::getConnection();
        $reportRepo = new PostgresReportRepository($pdo);
        $categoryRepo = new PostgresCategoryRepository($pdo);
        $this->reportUseCase = new GenerateReportUseCase($reportRepo, $categoryRepo);
    }

    public function sales(): void
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01'); // Primer día del mes
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $categoryId = !empty($_GET['category_id']) ? (int)$_GET['category_id'] : null;

        $reportData = $this->reportUseCase->generateSalesReport($startDate, $endDate, $categoryId);
        $reportData['title'] = 'Reporte Ejecutivo de Ventas e Ingresos | AURA Atelier';
        $reportData['user'] = SessionManager::getUser();

        // Exportación a archivo CSV
        if (($_GET['export'] ?? '') === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=reporte_ventas_aura_' . date('Ymd_His') . '.csv');
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM para Excel
            fputcsv($out, ['No. Pedido', 'Fecha', 'Cliente', 'Prendas', 'Total MXN']);
            foreach ($reportData['recent_orders'] as $ord) {
                fputcsv($out, [
                    $ord['order_number'],
                    $ord['created_at'],
                    $ord['customer_name'],
                    $ord['item_count'],
                    number_format((float)$ord['total_amount'], 2, '.', '')
                ]);
            }
            fclose($out);
            exit;
        }

        $layout = isset($_GET['print']) ? 'none' : 'admin';
        echo Router::renderView('reports/sales_report', $reportData, $layout);
    }

    public function inventory(): void
    {
        $categoryId = !empty($_GET['category_id']) ? (int)$_GET['category_id'] : null;
        $threshold = max(1, (int)($_GET['threshold'] ?? 10));

        $reportData = $this->reportUseCase->generateInventoryReport($categoryId, $threshold);
        $reportData['title'] = 'Reporte de Inventario y Stock Crítico | AURA Atelier';
        $reportData['user'] = SessionManager::getUser();

        // Exportación a archivo CSV
        if (($_GET['export'] ?? '') === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=reporte_inventario_aura_' . date('Ymd_His') . '.csv');
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($out, ['Prenda', 'Categoría', 'Talla', 'Color', 'SKU', 'Stock', 'Precio Unitario MXN', 'Valor Total MXN']);
            foreach ($reportData['full_inventory'] as $item) {
                fputcsv($out, [
                    $item['product_name'],
                    $item['category_name'],
                    $item['size'],
                    $item['color'],
                    $item['sku'],
                    $item['stock'],
                    number_format((float)$item['final_price'], 2, '.', ''),
                    number_format((float)$item['total_value'], 2, '.', '')
                ]);
            }
            fclose($out);
            exit;
        }

        $layout = isset($_GET['print']) ? 'none' : 'admin';
        echo Router::renderView('reports/inventory_report', $reportData, $layout);
    }

    public function sellers(): void
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $reportData = $this->reportUseCase->generateSellerReport($startDate, $endDate);
        $reportData['title'] = 'Reporte de Desempeño por Vendedor | AURA Atelier';
        $reportData['user'] = SessionManager::getUser();

        $layout = isset($_GET['print']) ? 'none' : 'admin';
        echo Router::renderView('reports/seller_report', $reportData, $layout);
    }
}
