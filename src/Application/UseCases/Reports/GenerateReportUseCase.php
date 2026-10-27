<?php

namespace App\Application\UseCases\Reports;

use App\Domain\Ports\CategoryRepositoryPort;
use App\Domain\Ports\ReportRepositoryPort;

class GenerateReportUseCase
{
    public function __construct(
        private ReportRepositoryPort $reportRepo,
        private CategoryRepositoryPort $categoryRepo
    ) {
    }

    public function generateSalesReport(?string $startDate = null, ?string $endDate = null, ?int $categoryId = null): array
    {
        $startDate = $startDate ?: date('Y-m-d', strtotime('-30 days'));
        $endDate = $endDate ?: date('Y-m-d');

        $reportData = $this->reportRepo->getSalesReport($startDate, $endDate, $categoryId);
        $categories = $this->categoryRepo->findAll();

        // Calcular porcentajes de ventas por categoría
        $totalSales = $reportData['summary']['total_sales'];
        foreach ($reportData['sales_by_category'] as &$cat) {
            $cat['percentage'] = $totalSales > 0 ? round(($cat['total_revenue'] / $totalSales) * 100, 1) : 0;
        }

        $reportData['categories'] = $categories;
        $reportData['selected_category_id'] = $categoryId;
        $reportData['generated_at'] = date('d/m/Y H:i:s');

        return $reportData;
    }

    public function generateInventoryReport(?int $categoryId = null, int $lowStockThreshold = 10): array
    {
        $reportData = $this->reportRepo->getInventoryReport($categoryId, $lowStockThreshold);
        $categories = $this->categoryRepo->findAll();

        // Calcular porcentajes de valor de inventario por categoría
        $totalVal = $reportData['summary']['total_inventory_value'];
        foreach ($reportData['stock_by_category'] as &$c) {
            $c['percentage'] = $totalVal > 0 ? round(($c['stock_value'] / $totalVal) * 100, 1) : 0;
        }

        $reportData['categories'] = $categories;
        $reportData['selected_category_id'] = $categoryId;
        $reportData['generated_at'] = date('d/m/Y H:i:s');

        return $reportData;
    }

    public function generateSellerReport(?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?: date('Y-m-d', strtotime('-30 days'));
        $endDate = $endDate ?: date('Y-m-d');

        $sellers = $this->reportRepo->getSellerPerformanceReport($startDate, $endDate);
        
        $totalRev = array_sum(array_column($sellers, 'total_revenue'));

        return [
            'sellers' => $sellers,
            'total_revenue' => $totalRev,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'generated_at' => date('d/m/Y H:i:s')
        ];
    }
}
