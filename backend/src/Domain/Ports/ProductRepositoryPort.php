<?php

namespace App\Backend\Domain\Ports;

use App\Backend\Domain\Entities\Product;
use App\Backend\Domain\Entities\ProductVariant;
use App\Backend\Domain\Entities\ProductImage;

interface ProductRepositoryPort
{
    /**
     * @param array $filters [category_id, size, color, min_price, max_price, search, seller_id, status]
     * @return Product[]
     */
    public function findWithFilters(array $filters = [], int $limit = 24, int $offset = 0): array;

    public function countWithFilters(array $filters = []): int;

    public function findById(string $id): ?Product;

    public function findBySlug(string $slug): ?Product;

    /**
     * @return Product[]
     */
    public function findBySellerId(string $sellerId): array;

    public function save(Product $product): Product;

    public function update(Product $product): bool;

    public function delete(string $productId, string $sellerId): bool;

    public function addVariant(ProductVariant $variant): ProductVariant;

    public function updateVariant(ProductVariant $variant): bool;

    public function deleteVariant(string $variantId): bool;

    public function findVariantById(string $variantId): ?ProductVariant;

    public function addImage(ProductImage $image): ProductImage;

    public function deleteImage(string $imageId): bool;

    public function decrementStock(string $variantId, int $quantity): bool;

    /**
     * Get distinct sizes and colors for filter menus
     */
    public function getAvailableFilterOptions(): array;
}
