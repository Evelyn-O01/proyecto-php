<?php

namespace App\Presentation\Controllers;

use App\Application\UseCases\Catalog\ListProductsUseCase;
use App\Infrastructure\Database\PostgresConnection;
use App\Infrastructure\Repositories\PostgresCategoryRepository;
use App\Infrastructure\Repositories\PostgresProductRepository;
use App\Presentation\Router;

class CatalogController
{
    private ListProductsUseCase $listProductsUseCase;

    public function __construct()
    {
        $pdo = PostgresConnection::getConnection();
        $productRepo = new PostgresProductRepository($pdo);
        $categoryRepo = new PostgresCategoryRepository($pdo);
        $this->listProductsUseCase = new ListProductsUseCase($productRepo, $categoryRepo);
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
        // Clean empty filters
        $filters = array_filter($filters, fn($val) => $val !== null && $val !== '');

        $page = max(1, (int)($_GET['page'] ?? 1));
        $data = $this->listProductsUseCase->execute($filters, $page, 12);
        $data['title'] = 'Catálogo Exclusivo | AURA Atelier';

        echo Router::renderView('store/catalog', $data, 'main');
    }
}
