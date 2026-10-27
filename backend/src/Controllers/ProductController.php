<?php

namespace App\Backend\Controllers;

use App\Backend\Domain\Entities\Product;
use App\Backend\Domain\Entities\ProductImage;
use App\Backend\Domain\Entities\ProductVariant;
use App\Backend\Domain\Ports\CategoryRepositoryPort;
use App\Backend\Domain\Ports\FileStoragePort;
use App\Backend\Domain\Ports\ProductRepositoryPort;
use App\Backend\Infrastructure\Database\PostgresConnection;
use App\Backend\Infrastructure\Repositories\PostgresCategoryRepository;
use App\Backend\Infrastructure\Repositories\PostgresProductRepository;
use App\Backend\Infrastructure\Storage\LocalStorageAdapter;
use RuntimeException;

class ProductController
{
    private ProductRepositoryPort $productRepo;
    private CategoryRepositoryPort $categoryRepo;
    private FileStoragePort $storage;

    public function __construct(
        ?ProductRepositoryPort $productRepo = null,
        ?CategoryRepositoryPort $categoryRepo = null,
        ?FileStoragePort $storage = null
    ) {
        $pdo = PostgresConnection::getConnection();
        $this->productRepo = $productRepo ?: new PostgresProductRepository($pdo);
        $this->categoryRepo = $categoryRepo ?: new PostgresCategoryRepository($pdo);
        $this->storage = $storage ?: new LocalStorageAdapter();
    }

    public function list(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $products = $this->productRepo->findWithFilters($filters, $perPage, $offset);
        $total = $this->productRepo->countWithFilters($filters);
        $categories = $this->categoryRepo->findAll();
        $filterOptions = $this->productRepo->getAvailableFilterOptions();

        $productsData = array_map(function (Product $p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'description' => $p->description,
                'base_price' => $p->basePrice,
                'category_id' => $p->categoryId,
                'category_name' => $p->categoryName,
                'seller_id' => $p->sellerId,
                'seller_name' => $p->sellerName,
                'primary_image' => $p->getPrimaryImage(),
                'total_stock' => $p->getTotalStock(),
                'available_sizes' => $p->getAvailableSizes(),
                'status' => $p->status,
            ];
        }, $products);

        return [
            'products' => $productsData,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int)ceil($total / $perPage),
            'categories' => array_map(fn($c) => $c->toArray(), $categories),
            'available_sizes' => $filterOptions['sizes'],
            'available_colors' => $filterOptions['colors'],
            'min_price' => $filterOptions['min_price'],
            'max_price' => $filterOptions['max_price'],
        ];
    }

    public function getDetail(string $slugOrId): array
    {
        $product = $this->productRepo->findBySlug($slugOrId);
        if (!$product) {
            $product = $this->productRepo->findById($slugOrId);
        }

        if (!$product) {
            throw new RuntimeException("Prenda no encontrada.");
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'base_price' => $product->basePrice,
            'category_id' => $product->categoryId,
            'category_name' => $product->categoryName,
            'seller_id' => $product->sellerId,
            'seller_name' => $product->sellerName,
            'primary_image' => $product->getPrimaryImage(),
            'total_stock' => $product->getTotalStock(),
            'status' => $product->status,
            'variants' => array_map(fn(ProductVariant $v) => $v->toArray(), $product->variants),
            'images' => array_map(fn(ProductImage $i) => $i->toArray(), $product->images),
        ];
    }

    public function getCategories(): array
    {
        $categories = $this->categoryRepo->findAll();
        return array_map(fn($c) => $c->toArray(), $categories);
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
        foreach ($variantsData as $v) {
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
        string $status = 'ACTIVE',
        array $variantStocks = []
    ): Product {
        $existing = $this->productRepo->findById($productId);
        if (!$existing) {
            throw new RuntimeException("Producto no encontrado.");
        }
        if (!empty($sellerId) && $existing->sellerId !== $sellerId) {
            throw new RuntimeException("Sin autorización sobre este producto.");
        }

        $updatedProduct = new Product(
            id: $productId,
            sellerId: $existing->sellerId,
            categoryId: $categoryId,
            name: trim($name),
            slug: $existing->slug,
            description: trim($description),
            basePrice: $basePrice,
            status: $status
        );

        $this->productRepo->update($updatedProduct);

        if (!empty($variantStocks)) {
            foreach ($variantStocks as $vId => $stk) {
                $variant = $this->productRepo->findVariantById((string)$vId);
                if ($variant && $variant->productId === $productId) {
                    $variant->stock = max(0, (int)$stk);
                    $this->productRepo->updateVariant($variant);
                }
            }
        }

        return $this->productRepo->findById($productId);
    }

    public function deleteProduct(string $productId, string $sellerId): bool
    {
        return $this->productRepo->delete($productId, $sellerId);
    }

    public function getBySeller(string $sellerId): array
    {
        $products = $this->productRepo->findBySellerId($sellerId);
        return array_map(function (Product $p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'base_price' => $p->basePrice,
                'category_name' => $p->categoryName,
                'primary_image' => $p->getPrimaryImage(),
                'total_stock' => $p->getTotalStock(),
                'status' => $p->status,
                'variants' => array_map(fn($v) => $v->toArray(), $p->variants),
            ];
        }, $products);
    }

    private function generateSlug(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        $original = $slug;
        $counter = 1;
        while ($this->productRepo->findBySlug($slug)) {
            $slug = $original . '-' . $counter;
            $counter++;
        }
        return $slug;
    }
}
