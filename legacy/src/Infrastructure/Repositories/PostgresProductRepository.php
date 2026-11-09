<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Product;
use App\Domain\Entities\ProductVariant;
use App\Domain\Entities\ProductImage;
use App\Domain\Ports\ProductRepositoryPort;
use PDO;

class PostgresProductRepository implements ProductRepositoryPort
{
    public function __construct(private PDO $db)
    {
    }

    public function findWithFilters(array $filters = [], int $limit = 24, int $offset = 0): array
    {
        $params = [];
        $where = ["p.status = 'ACTIVE'"];

        if (!empty($filters['status'])) {
            $where[0] = "p.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = :category_id";
            $params['category_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['category_slug'])) {
            $where[] = "c.slug = :category_slug";
            $params['category_slug'] = $filters['category_slug'];
        }

        if (!empty($filters['seller_id'])) {
            $where[] = "p.seller_id = :seller_id";
            $params['seller_id'] = $filters['seller_id'];
        }

        if (!empty($filters['min_price'])) {
            $where[] = "p.base_price >= :min_price";
            $params['min_price'] = (float)$filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $where[] = "p.base_price <= :max_price";
            $params['max_price'] = (float)$filters['max_price'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(LOWER(p.name) LIKE :search OR LOWER(p.description) LIKE :search)";
            $params['search'] = '%' . strtolower(trim($filters['search'])) . '%';
        }

        if (!empty($filters['size'])) {
            $where[] = "EXISTS (SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id AND pv.size = :size AND pv.stock > 0)";
            $params['size'] = $filters['size'];
        }

        if (!empty($filters['color'])) {
            $where[] = "EXISTS (SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id AND LOWER(pv.color) = LOWER(:color) AND pv.stock > 0)";
            $params['color'] = $filters['color'];
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT p.*, c.name AS category_name, u.name AS seller_name
            FROM products p
            INNER JOIN categories c ON p.category_id = c.id
            INNER JOIN users u ON p.seller_id = u.id
            WHERE {$whereClause}
            ORDER BY p.created_at DESC
            LIMIT {$limit} OFFSET {$offset}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return array_map(fn($row) => $this->hydrateProductWithRelations($row), $rows);
    }

    public function countWithFilters(array $filters = []): int
    {
        $params = [];
        $where = ["p.status = 'ACTIVE'"];

        if (!empty($filters['status'])) {
            $where[0] = "p.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = :category_id";
            $params['category_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['category_slug'])) {
            $where[] = "c.slug = :category_slug";
            $params['category_slug'] = $filters['category_slug'];
        }

        if (!empty($filters['seller_id'])) {
            $where[] = "p.seller_id = :seller_id";
            $params['seller_id'] = $filters['seller_id'];
        }

        if (!empty($filters['min_price'])) {
            $where[] = "p.base_price >= :min_price";
            $params['min_price'] = (float)$filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $where[] = "p.base_price <= :max_price";
            $params['max_price'] = (float)$filters['max_price'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(LOWER(p.name) LIKE :search OR LOWER(p.description) LIKE :search)";
            $params['search'] = '%' . strtolower(trim($filters['search'])) . '%';
        }

        if (!empty($filters['size'])) {
            $where[] = "EXISTS (SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id AND pv.size = :size AND pv.stock > 0)";
            $params['size'] = $filters['size'];
        }

        if (!empty($filters['color'])) {
            $where[] = "EXISTS (SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id AND LOWER(pv.color) = LOWER(:color) AND pv.stock > 0)";
            $params['color'] = $filters['color'];
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT COUNT(p.id)
            FROM products p
            INNER JOIN categories c ON p.category_id = c.id
            WHERE {$whereClause}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function findById(string $id): ?Product
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS category_name, u.name AS seller_name
            FROM products p
            INNER JOIN categories c ON p.category_id = c.id
            INNER JOIN users u ON p.seller_id = u.id
            WHERE p.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return $this->hydrateProductWithRelations($row);
    }

    public function findBySlug(string $slug): ?Product
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS category_name, u.name AS seller_name
            FROM products p
            INNER JOIN categories c ON p.category_id = c.id
            INNER JOIN users u ON p.seller_id = u.id
            WHERE p.slug = :slug
        ");
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return $this->hydrateProductWithRelations($row);
    }

    public function findBySellerId(string $sellerId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS category_name, u.name AS seller_name
            FROM products p
            INNER JOIN categories c ON p.category_id = c.id
            INNER JOIN users u ON p.seller_id = u.id
            WHERE p.seller_id = :seller_id
            ORDER BY p.created_at DESC
        ");
        $stmt->execute(['seller_id' => $sellerId]);
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => $this->hydrateProductWithRelations($row), $rows);
    }

    public function save(Product $product): Product
    {
        $stmt = $this->db->prepare("
            INSERT INTO products (id, seller_id, category_id, name, slug, description, base_price, status)
            VALUES (COALESCE(:id, uuid_generate_v4()), :seller_id, :category_id, :name, :slug, :description, :base_price, :status)
            RETURNING id, created_at, updated_at
        ");
        $stmt->execute([
            'id' => $product->id,
            'seller_id' => $product->sellerId,
            'category_id' => $product->categoryId,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'base_price' => $product->basePrice,
            'status' => $product->status,
        ]);
        $row = $stmt->fetch();
        $savedId = $row['id'];

        return $this->findById($savedId);
    }

    public function update(Product $product): bool
    {
        $stmt = $this->db->prepare("
            UPDATE products SET
                category_id = :category_id,
                name = :name,
                slug = :slug,
                description = :description,
                base_price = :base_price,
                status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id AND seller_id = :seller_id
        ");
        return $stmt->execute([
            'id' => $product->id,
            'seller_id' => $product->sellerId,
            'category_id' => $product->categoryId,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'base_price' => $product->basePrice,
            'status' => $product->status,
        ]);
    }

    public function delete(string $productId, string $sellerId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = :id AND seller_id = :seller_id");
        return $stmt->execute([
            'id' => $productId,
            'seller_id' => $sellerId
        ]);
    }

    public function addVariant(ProductVariant $variant): ProductVariant
    {
        $stmt = $this->db->prepare("
            INSERT INTO product_variants (id, product_id, size, color, color_code, sku, stock, additional_price)
            VALUES (COALESCE(:id, uuid_generate_v4()), :product_id, :size, :color, :color_code, :sku, :stock, :additional_price)
            RETURNING id, created_at
        ");
        $stmt->execute([
            'id' => $variant->id,
            'product_id' => $variant->productId,
            'size' => $variant->size,
            'color' => $variant->color,
            'color_code' => $variant->colorCode,
            'sku' => $variant->sku,
            'stock' => $variant->stock,
            'additional_price' => $variant->additionalPrice,
        ]);
        $row = $stmt->fetch();
        return new ProductVariant(
            id: $row['id'],
            productId: $variant->productId,
            size: $variant->size,
            color: $variant->color,
            colorCode: $variant->colorCode,
            sku: $variant->sku,
            stock: $variant->stock,
            additionalPrice: $variant->additionalPrice,
            createdAt: $row['created_at']
        );
    }

    public function updateVariant(ProductVariant $variant): bool
    {
        $stmt = $this->db->prepare("
            UPDATE product_variants SET
                size = :size,
                color = :color,
                color_code = :color_code,
                sku = :sku,
                stock = :stock,
                additional_price = :additional_price
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $variant->id,
            'size' => $variant->size,
            'color' => $variant->color,
            'color_code' => $variant->colorCode,
            'sku' => $variant->sku,
            'stock' => $variant->stock,
            'additional_price' => $variant->additionalPrice,
        ]);
    }

    public function deleteVariant(string $variantId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM product_variants WHERE id = :id");
        return $stmt->execute(['id' => $variantId]);
    }

    public function findVariantById(string $variantId): ?ProductVariant
    {
        $stmt = $this->db->prepare("SELECT * FROM product_variants WHERE id = :id");
        $stmt->execute(['id' => $variantId]);
        $row = $stmt->fetch();
        return $row ? ProductVariant::fromArray($row) : null;
    }

    public function addImage(ProductImage $image): ProductImage
    {
        $stmt = $this->db->prepare("
            INSERT INTO product_images (id, product_id, image_path, is_primary, sort_order)
            VALUES (COALESCE(:id, uuid_generate_v4()), :product_id, :image_path, :is_primary, :sort_order)
            RETURNING id, created_at
        ");
        $stmt->execute([
            'id' => $image->id,
            'product_id' => $image->productId,
            'image_path' => $image->imagePath,
            'is_primary' => $image->isPrimary ? 1 : 0,
            'sort_order' => $image->sortOrder,
        ]);
        $row = $stmt->fetch();
        return new ProductImage(
            id: $row['id'],
            productId: $image->productId,
            imagePath: $image->imagePath,
            isPrimary: $image->isPrimary,
            sortOrder: $image->sortOrder,
            createdAt: $row['created_at']
        );
    }

    public function deleteImage(string $imageId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM product_images WHERE id = :id");
        return $stmt->execute(['id' => $imageId]);
    }

    public function decrementStock(string $variantId, int $quantity): bool
    {
        $stmt = $this->db->prepare("
            UPDATE product_variants
            SET stock = stock - :quantity
            WHERE id = :id AND stock >= :quantity
        ");
        $stmt->execute([
            'id' => $variantId,
            'quantity' => $quantity,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function getAvailableFilterOptions(): array
    {
        $sizesStmt = $this->db->query("
            SELECT DISTINCT size FROM product_variants WHERE stock > 0 ORDER BY size ASC
        ");
        $sizes = $sizesStmt->fetchAll(PDO::FETCH_COLUMN);

        $colorsStmt = $this->db->query("
            SELECT DISTINCT color, color_code FROM product_variants WHERE stock > 0 ORDER BY color ASC
        ");
        $colors = $colorsStmt->fetchAll();

        $priceStmt = $this->db->query("
            SELECT MIN(base_price) as min_price, MAX(base_price) as max_price FROM products WHERE status = 'ACTIVE'
        ");
        $price = $priceStmt->fetch();

        return [
            'sizes' => $sizes,
            'colors' => $colors,
            'min_price' => (float)($price['min_price'] ?? 0),
            'max_price' => (float)($price['max_price'] ?? 500)
        ];
    }

    private function hydrateProductWithRelations(array $row): Product
    {
        // Fetch variants
        $vStmt = $this->db->prepare("SELECT * FROM product_variants WHERE product_id = :id ORDER BY created_at ASC");
        $vStmt->execute(['id' => $row['id']]);
        $variantRows = $vStmt->fetchAll();
        $variants = array_map(fn($v) => ProductVariant::fromArray($v), $variantRows);

        // Fetch images
        $iStmt = $this->db->prepare("SELECT * FROM product_images WHERE product_id = :id ORDER BY is_primary DESC, sort_order ASC");
        $iStmt->execute(['id' => $row['id']]);
        $imageRows = $iStmt->fetchAll();
        $images = array_map(fn($i) => ProductImage::fromArray($i), $imageRows);

        return Product::fromArray($row, $variants, $images);
    }
}
