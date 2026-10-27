<?php

namespace App\Backend\Endpoints;

use App\Backend\Controllers\ReportController;

class ReportEndpoints
{
    private ReportController $controller;

    public function __construct()
    {
        $this->controller = new ReportController();
    }

    public function sales(): void
    {
        $start = $_GET['start_date'] ?? null;
        $end = $_GET['end_date'] ?? null;
        $catId = !empty($_GET['category_id']) ? (int)$_GET['category_id'] : null;

        $data = $this->controller->getSalesReport($start, $end, $catId);
        ApiRouter::json(['success' => true, 'data' => $data]);
    }

    public function inventory(): void
    {
        $catId = !empty($_GET['category_id']) ? (int)$_GET['category_id'] : null;
        $threshold = max(1, (int)($_GET['threshold'] ?? 10));

        $data = $this->controller->getInventoryReport($catId, $threshold);
        ApiRouter::json(['success' => true, 'data' => $data]);
    }

    public function sellers(): void
    {
        $start = $_GET['start_date'] ?? null;
        $end = $_GET['end_date'] ?? null;

        $data = $this->controller->getSellerReport($start, $end);
        ApiRouter::json(['success' => true, 'data' => $data]);
    }
}
