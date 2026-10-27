<?php

namespace App\Backend\Controllers;

use App\Backend\Domain\Ports\CategoryRepositoryPort;
use App\Backend\Domain\Ports\ReportRepositoryPort;
use App\Backend\Infrastructure\Database\PostgresConnection;
use App\Backend\Infrastructure\Repositories\PostgresCategoryRepository;
use App\Backend\Infrastructure\Repositories\PostgresReportRepository;

class ReportController
{
    private ReportRepositoryPort $reportRepo;
    private CategoryRepositoryPort $categoryRepo;

    public function __construct(
        ?ReportRepositoryPort $reportRepo = null,
        ?CategoryRepositoryPort $categoryRepo = null
    ) {
        $pdo = PostgresConnection::getConnection();
        $this->reportRepo = $reportRepo ?: new PostgresReportRepository($pdo);
        $this->categoryRepo = $categoryRepo ?: new PostgresCategoryRepository($pdo);
    }

    public function getSalesReport(?string $startDate = null, ?string $endDate = null, ?int $categoryId = null): array
    {
        $startDate = $startDate ?: date('Y-m-01');
        $endDate = $endDate ?: date('Y-m-d');

        $data = $this->reportRepo->getSalesReport($startDate, $endDate, $categoryId);
        $categories = $this->categoryRepo->findAll();

        $totalSales = $data['summary']['total_sales'];
        foreach ($data['sales_by_category'] as &$cat) {
            $cat['percentage'] = $totalSales > 0 ? round(($cat['total_revenue'] / $totalSales) * 100, 1) : 0;
        }

        $data['categories'] = array_map(fn($c) => $c->toArray(), $categories);
        $data['selected_category_id'] = $categoryId;
        $data['generated_at'] = date('d/m/Y H:i:s');

        return $data;
    }

    public function getInventoryReport(?int $categoryId = null, int $threshold = 10): array
    {
        $data = $this->reportRepo->getInventoryReport($categoryId, $threshold);
        $categories = $this->categoryRepo->findAll();

        $totalVal = $data['summary']['total_inventory_value'];
        foreach ($data['stock_by_category'] as &$c) {
            $c['percentage'] = $totalVal > 0 ? round(($c['stock_value'] / $totalVal) * 100, 1) : 0;
        }

        $data['categories'] = array_map(fn($c) => $c->toArray(), $categories);
        $data['selected_category_id'] = $categoryId;
        $data['generated_at'] = date('d/m/Y H:i:s');

        return $data;
    }

    public function getSellerReport(?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?: date('Y-m-01');
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
