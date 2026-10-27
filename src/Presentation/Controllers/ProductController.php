<?php

namespace App\Presentation\Controllers;

use App\Application\UseCases\Catalog\GetProductDetailUseCase;
use App\Infrastructure\Database\PostgresConnection;
use App\Infrastructure\Repositories\PostgresProductRepository;
use App\Presentation\Router;

class ProductController
{
    private GetProductDetailUseCase $getProductDetailUseCase;

    public function __construct()
    {
        $pdo = PostgresConnection::getConnection();
        $productRepo = new PostgresProductRepository($pdo);
        $this->getProductDetailUseCase = new GetProductDetailUseCase($productRepo);
    }

    public function show(array $params): void
    {
        $slug = $params['slug'] ?? '';
        try {
            $product = $this->getProductDetailUseCase->execute($slug);
            $data = [
                'title' => "{$product->name} | AURA Atelier",
                'product' => $product
            ];
            echo Router::renderView('store/product_detail', $data, 'main');
        } catch (\Throwable $e) {
            http_response_code(404);
            echo Router::renderView('store/404', ['title' => 'Producto no encontrado | AURA'], 'main');
        }
    }
}
