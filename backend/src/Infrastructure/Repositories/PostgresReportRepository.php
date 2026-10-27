<?php

namespace App\Backend\Infrastructure\Repositories;

use App\Backend\Domain\Ports\ReportRepositoryPort;
use PDO;

class PostgresReportRepository implements ReportRepositoryPort
{
    public function __construct(private PDO $db)
    {
    }

    public function getSalesReport(string $startDate, string $endDate, ?int $categoryId = null): array
    {
        // 1. KPI Summary
        $catFilter = $categoryId ? "AND p.category_id = :cat_id" : "";
        $params = [
            'start' => $startDate . ' 00:00:00',
            'end' => $endDate . ' 23:59:59',
        ];
        if ($categoryId) {
            $params['cat_id'] = $categoryId;
        }

        $summarySql = "
            SELECT 
                COALESCE(SUM(oi.subtotal), 0) as total_sales,
                COUNT(DISTINCT o.id) as total_orders,
                COALESCE(SUM(oi.quantity), 0) as total_items_sold,
                CASE WHEN COUNT(DISTINCT o.id) > 0 
                     THEN COALESCE(SUM(oi.subtotal), 0) / COUNT(DISTINCT o.id) 
                     ELSE 0 END as avg_ticket
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            JOIN product_variants pv ON oi.product_variant_id = pv.id
            JOIN products p ON pv.product_id = p.id
            WHERE o.status = 'PAID'
              AND o.created_at BETWEEN :start AND :end
              {$catFilter}
        ";
        $stmt = $this->db->prepare($summarySql);
        $stmt->execute($params);
        $summary = $stmt->fetch();

        // 2. Sales by Date (for trend chart)
        $dateSql = "
            SELECT 
                TO_CHAR(o.created_at, 'YYYY-MM-DD') as date_label,
                COUNT(DISTINCT o.id) as orders_count,
                SUM(oi.subtotal) as total_amount
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            JOIN product_variants pv ON oi.product_variant_id = pv.id
            JOIN products p ON pv.product_id = p.id
            WHERE o.status = 'PAID'
              AND o.created_at BETWEEN :start AND :end
              {$catFilter}
            GROUP BY TO_CHAR(o.created_at, 'YYYY-MM-DD')
            ORDER BY date_label ASC
        ";
        $stmt = $this->db->prepare($dateSql);
        $stmt->execute($params);
        $salesByDate = $stmt->fetchAll();

        // 3. Sales by Category
        $catSql = "
            SELECT 
                c.id,
                c.name as category_name,
                COUNT(oi.id) as items_sold,
                SUM(oi.subtotal) as total_revenue
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN product_variants pv ON oi.product_variant_id = pv.id
            JOIN products p ON pv.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            WHERE o.status = 'PAID'
              AND o.created_at BETWEEN :start AND :end
              {$catFilter}
            GROUP BY c.id, c.name
            ORDER BY total_revenue DESC
        ";
        $stmt = $this->db->prepare($catSql);
        $stmt->execute($params);
        $salesByCategory = $stmt->fetchAll();

        // 4. Top Selling Products
        $topProductsSql = "
            SELECT 
                p.id,
                p.name as product_name,
                c.name as category_name,
                SUM(oi.quantity) as total_quantity,
                SUM(oi.subtotal) as total_revenue
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN product_variants pv ON oi.product_variant_id = pv.id
            JOIN products p ON pv.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            WHERE o.status = 'PAID'
              AND o.created_at BETWEEN :start AND :end
              {$catFilter}
            GROUP BY p.id, p.name, c.name
            ORDER BY total_revenue DESC
            LIMIT 10
        ";
        $stmt = $this->db->prepare($topProductsSql);
        $stmt->execute($params);
        $topProducts = $stmt->fetchAll();

        // 5. Payment Methods
        $paySql = "
            SELECT 
                COALESCE(pm.payment_method, 'Tarjeta') as payment_method,
                COUNT(pm.id) as transaction_count,
                SUM(pm.amount) as total_processed
            FROM payments pm
            JOIN orders o ON pm.order_id = o.id
            WHERE pm.status = 'APPROVED'
              AND o.created_at BETWEEN :start AND :end
            GROUP BY payment_method
            ORDER BY total_processed DESC
        ";
        $stmt = $this->db->prepare($paySql);
        $stmt->execute(['start' => $params['start'], 'end' => $params['end']]);
        $paymentMethods = $stmt->fetchAll();

        // 6. Recent Paid Orders list
        $ordersSql = "
            SELECT 
                o.id,
                o.order_number,
                u.name as customer_name,
                o.total_amount,
                o.created_at,
                (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
            FROM orders o
            JOIN users u ON o.customer_id = u.id
            WHERE o.status = 'PAID'
              AND o.created_at BETWEEN :start AND :end
            ORDER BY o.created_at DESC
            LIMIT 25
        ";
        $stmt = $this->db->prepare($ordersSql);
        $stmt->execute(['start' => $params['start'], 'end' => $params['end']]);
        $recentOrders = $stmt->fetchAll();

        return [
            'summary' => [
                'total_sales' => (float)$summary['total_sales'],
                'total_orders' => (int)$summary['total_orders'],
                'avg_ticket' => (float)$summary['avg_ticket'],
                'total_items_sold' => (int)$summary['total_items_sold'],
            ],
            'sales_by_date' => $salesByDate,
            'sales_by_category' => $salesByCategory,
            'top_products' => $topProducts,
            'payment_methods' => $paymentMethods,
            'recent_orders' => $recentOrders,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    public function getInventoryReport(?int $categoryId = null, int $lowStockThreshold = 10): array
    {
        $catFilter = $categoryId ? "WHERE p.category_id = :cat_id" : "";
        $params = [];
        if ($categoryId) {
            $params['cat_id'] = $categoryId;
        }

        // Summary
        $summarySql = "
            SELECT 
                COUNT(DISTINCT p.id) as total_products,
                COUNT(pv.id) as total_variants,
                COALESCE(SUM(pv.stock), 0) as total_units,
                COALESCE(SUM(pv.stock * (p.base_price + pv.additional_price)), 0) as total_inventory_value,
                COUNT(CASE WHEN pv.stock <= {$lowStockThreshold} THEN 1 END) as low_stock_count
            FROM products p
            JOIN product_variants pv ON pv.product_id = p.id
            {$catFilter}
        ";
        $stmt = $this->db->prepare($summarySql);
        $stmt->execute($params);
        $summary = $stmt->fetch();

        // Low stock items
        $lowStockSql = "
            SELECT 
                p.id as product_id,
                p.name as product_name,
                c.name as category_name,
                pv.size,
                pv.color,
                pv.sku,
                pv.stock,
                p.base_price + pv.additional_price as final_price,
                u.name as seller_name
            FROM product_variants pv
            JOIN products p ON pv.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            JOIN users u ON p.seller_id = u.id
            WHERE pv.stock <= :threshold
              " . ($categoryId ? "AND p.category_id = :cat_id" : "") . "
            ORDER BY pv.stock ASC, p.name ASC
        ";
        $lowParams = ['threshold' => $lowStockThreshold];
        if ($categoryId) {
            $lowParams['cat_id'] = $categoryId;
        }
        $stmt = $this->db->prepare($lowStockSql);
        $stmt->execute($lowParams);
        $lowStockItems = $stmt->fetchAll();

        // Stock by category
        $catStockSql = "
            SELECT 
                c.id,
                c.name as category_name,
                COUNT(DISTINCT p.id) as product_count,
                SUM(pv.stock) as total_units,
                SUM(pv.stock * (p.base_price + pv.additional_price)) as stock_value
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id
            LEFT JOIN product_variants pv ON pv.product_id = p.id
            GROUP BY c.id, c.name
            ORDER BY stock_value DESC
        ";
        $stmt = $this->db->query($catStockSql);
        $stockByCategory = $stmt->fetchAll();

        // Full inventory list
        $fullSql = "
            SELECT 
                p.name as product_name,
                c.name as category_name,
                pv.size,
                pv.color,
                pv.sku,
                pv.stock,
                p.base_price + pv.additional_price as final_price,
                pv.stock * (p.base_price + pv.additional_price) as total_value,
                p.status
            FROM products p
            JOIN categories c ON p.category_id = c.id
            JOIN product_variants pv ON pv.product_id = p.id
            {$catFilter}
            ORDER BY c.name ASC, p.name ASC, pv.size ASC
        ";
        $stmt = $this->db->prepare($fullSql);
        $stmt->execute($params);
        $fullInventory = $stmt->fetchAll();

        return [
            'summary' => [
                'total_products' => (int)$summary['total_products'],
                'total_variants' => (int)$summary['total_variants'],
                'total_units' => (int)$summary['total_units'],
                'total_inventory_value' => (float)$summary['total_inventory_value'],
                'low_stock_count' => (int)$summary['low_stock_count'],
            ],
            'low_stock_items' => $lowStockItems,
            'stock_by_category' => $stockByCategory,
            'full_inventory' => $fullInventory,
            'low_stock_threshold' => $lowStockThreshold,
        ];
    }

    public function getSellerPerformanceReport(string $startDate, string $endDate): array
    {
        $sql = "
            SELECT 
                u.id as seller_id,
                u.name as seller_name,
                u.email as seller_email,
                COUNT(DISTINCT p.id) as total_products,
                COUNT(DISTINCT o.id) as total_orders,
                COALESCE(SUM(oi.quantity), 0) as units_sold,
                COALESCE(SUM(oi.subtotal), 0) as total_revenue
            FROM users u
            LEFT JOIN products p ON p.seller_id = u.id
            LEFT JOIN product_variants pv ON pv.product_id = p.id
            LEFT JOIN order_items oi ON oi.product_variant_id = pv.id
            LEFT JOIN orders o ON oi.order_id = o.id AND o.status = 'PAID' 
                 AND o.created_at BETWEEN :start AND :end
            WHERE u.role = 'SELLER'
            GROUP BY u.id, u.name, u.email
            ORDER BY total_revenue DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'start' => $startDate . ' 00:00:00',
            'end' => $endDate . ' 23:59:59',
        ]);
        return $stmt->fetchAll();
    }
}
