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
