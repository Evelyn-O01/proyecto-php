<?php

namespace App\Backend\Domain\Ports;

interface ReportRepositoryPort
{
    /**
     * @return array{
     *   summary: array{total_sales: float, total_orders: int, avg_ticket: float, total_items_sold: int},
     *   sales_by_date: array,
     *   sales_by_category: array,
     *   top_products: array,
     *   payment_methods: array,
     *   recent_orders: array
     * }
     */
    public function getSalesReport(string $startDate, string $endDate, ?int $categoryId = null): array;

    /**
     * @return array{
     *   summary: array{total_products: int, total_variants: int, total_units: int, total_inventory_value: float, low_stock_count: int},
     *   low_stock_items: array,
     *   stock_by_category: array,
     *   full_inventory: array
     * }
     */
    public function getInventoryReport(?int $categoryId = null, int $lowStockThreshold = 10): array;

    /**
     * @return array
     */
    public function getSellerPerformanceReport(string $startDate, string $endDate): array;
}
