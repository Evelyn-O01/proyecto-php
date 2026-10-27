<?php

namespace App\Backend\Endpoints;

use App\Backend\Controllers\ProductController;

class CatalogEndpoints
{
    private ProductController $controller;

    public function __construct()
    {
        $this->controller = new ProductController();
    }

    public function index(): void
    {
        $filters = [
            'category_id' => $_GET['category_id'] ?? null,
            'category_slug' => $_GET['category'] ?? null,
            'size' => $_GET['size'] ?? null,
            'color' => $_GET['color'] ?? null,
            'min_price' => $_GET['min_price'] ?? null,
            'max_price' => $_GET['max_price'] ?? null,
            'search' => $_GET['q'] ?? null,
        ];
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = max(1, (int)($_GET['per_page'] ?? 12));

        $data = $this->controller->list($filters, $page, $perPage);
        ApiRouter::json(['success' => true, 'data' => $data]);
    }

    public function show(array $params): void
    {
        $slug = $params['slug'] ?? '';
        try {
            $data = $this->controller->getDetail($slug);
            ApiRouter::json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            ApiRouter::json(['success' => false, 'error' => $e->getMessage()], 404);
        }
    }

    public function categories(): void
    {
        $data = $this->controller->getCategories();
        ApiRouter::json(['success' => true, 'data' => $data]);
    }
}
