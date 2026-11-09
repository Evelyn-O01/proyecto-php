<?php

namespace App\Application\UseCases\Catalog;

use App\Domain\Entities\Product;
use App\Domain\Ports\ProductRepositoryPort;
use RuntimeException;

class GetProductDetailUseCase
{
    public function __construct(private ProductRepositoryPort $productRepo)
    {
    }

    public function execute(string $slugOrId): Product
    {
        $product = $this->productRepo->findBySlug($slugOrId);
        if (!$product) {
            $product = $this->productRepo->findById($slugOrId);
        }

        if (!$product) {
            throw new RuntimeException("Prenda de vestir no encontrada.");
        }

        return $product;
    }
}
