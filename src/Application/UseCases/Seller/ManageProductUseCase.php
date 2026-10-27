<?php

namespace App\Application\UseCases\Seller;

use App\Domain\Entities\Product;
use App\Domain\Entities\ProductImage;
use App\Domain\Entities\ProductVariant;
use App\Domain\Ports\FileStoragePort;
use App\Domain\Ports\ProductRepositoryPort;
use RuntimeException;

class ManageProductUseCase
{
    public function __construct(
        private ProductRepositoryPort $productRepo,
        private ?FileStoragePort $fileStorage = null
    ) {
    }

    public function createProduct(
        string $sellerId,
        int $categoryId,
        string $name,
        string $description,
        float $basePrice,
        array $variantsData = [],
        array $imagesData = []
    ): Product {
        if ($basePrice <= 0) {
            throw new RuntimeException("El precio base debe ser mayor a 0.");
        }

        $slug = $this->generateSlug($name);

        $product = new Product(
            id: null,
            sellerId: $sellerId,
            categoryId: $categoryId,
            name: trim($name),
            slug: $slug,
            description: trim($description),
            basePrice: $basePrice,
            status: 'ACTIVE'
        );

        $savedProduct = $this->productRepo->save($product);

        // Guardar variantes
        foreach ($variantsData as $index => $v) {
            if (empty($v['size']) || empty($v['color'])) {
                continue;
            }
            $sku = !empty($v['sku']) ? $v['sku'] : strtoupper(substr($slug, 0, 4)) . '-' . strtoupper($v['size']) . '-' . rand(100, 999);
            $variant = new ProductVariant(
                id: null,
                productId: $savedProduct->id,
                size: trim($v['size']),
                color: trim($v['color']),
                colorCode: $v['color_code'] ?? '#000000',
                sku: $sku,
                stock: max(0, (int)($v['stock'] ?? 0)),
                additionalPrice: max(0.0, (float)($v['additional_price'] ?? 0.0))
            );
            $this->productRepo->addVariant($variant);
        }

        // Guardar imágenes
        foreach ($imagesData as $idx => $img) {
            $path = is_array($img) ? ($img['path'] ?? '') : $img;
            if (empty($path)) continue;

            $image = new ProductImage(
                id: null,
                productId: $savedProduct->id,
                imagePath: $path,
                isPrimary: $idx === 0,
                sortOrder: $idx + 1
            );
            $this->productRepo->addImage($image);
        }

        return $this->productRepo->findById($savedProduct->id);
    }

    public function updateProduct(
        string $productId,
        string $sellerId,
        int $categoryId,
        string $name,
        string $description,
        float $basePrice,
        string $status = 'ACTIVE'
    ): Product {
        $existing = $this->productRepo->findById($productId);
        if (!$existing) {
            throw new RuntimeException("Producto no encontrado.");
        }
        if ($existing->sellerId !== $sellerId) {
            throw new RuntimeException("No tienes permisos para modificar este producto.");
        }

        $updatedProduct = new Product(
            id: $productId,
            sellerId: $sellerId,
            categoryId: $categoryId,
            name: trim($name),
            slug: $this->generateSlug($name, $productId),
            description: trim($description),
            basePrice: $basePrice,
            status: $status
        );

        $this->productRepo->update($updatedProduct);
        return $this->productRepo->findById($productId);
    }

    public function deleteProduct(string $productId, string $sellerId): bool
    {
        return $this->productRepo->delete($productId, $sellerId);
    }

    private function generateSlug(string $title, ?string $excludeId = null): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        $originalSlug = $slug;
        $counter = 1;

        while ($found = $this->productRepo->findBySlug($slug)) {
            if ($excludeId && $found->id === $excludeId) {
                break;
            }
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
