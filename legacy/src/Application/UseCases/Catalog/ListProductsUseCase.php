<?php

namespace App\Application\UseCases\Catalog;

use App\Domain\Ports\CategoryRepositoryPort;
use App\Domain\Ports\ProductRepositoryPort;

class ListProductsUseCase
{
    public function __construct(
        private ProductRepositoryPort $productRepo,
        private CategoryRepositoryPort $categoryRepo
    ) {
    }

    public function execute(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $offset = ($page - 1) * $perPage;
        $products = $this->productRepo->findWithFilters($filters, $perPage, $offset);
        $total = $this->productRepo->countWithFilters($filters);
        $categories = $this->categoryRepo->findAll();
        $filterOptions = $this->productRepo->getAvailableFilterOptions();

        return [
            'products' => $products,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int)ceil($total / $perPage),
            'categories' => $categories,
            'available_sizes' => $filterOptions['sizes'],
            'available_colors' => $filterOptions['colors'],
            'min_price' => $filterOptions['min_price'],
            'max_price' => $filterOptions['max_price'],
            'current_filters' => $filters,
        ];
    }
}
