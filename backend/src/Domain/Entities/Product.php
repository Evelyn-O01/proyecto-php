<?php

namespace App\Backend\Domain\Entities;

class Product
{
    /**
     * @param ProductVariant[] $variants
     * @param ProductImage[] $images
     */
    public function __construct(
        public readonly ?string $id,
        public string $sellerId,
        public int $categoryId,
        public string $name,
        public string $slug,
        public string $description,
        public float $basePrice,
        public string $status = 'ACTIVE', // DRAFT, ACTIVE, OUT_OF_STOCK
        public ?string $categoryName = null,
        public ?string $sellerName = null,
        public array $variants = [],
        public array $images = [],
        public ?string $createdAt = null,
        public ?string $updatedAt = null
    ) {
    }

    public static function fromArray(array $data, array $variants = [], array $images = []): self
    {
        return new self(
            id: $data['id'] ?? null,
            sellerId: $data['seller_id'],
            categoryId: (int)$data['category_id'],
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? '',
            basePrice: (float)$data['base_price'],
            status: $data['status'] ?? 'ACTIVE',
            categoryName: $data['category_name'] ?? null,
            sellerName: $data['seller_name'] ?? null,
            variants: $variants,
            images: $images,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null
        );
    }

    public function getPrimaryImage(): string
    {
        foreach ($this->images as $img) {
            if ($img->isPrimary) {
                return $img->imagePath;
            }
        }
        if (!empty($this->images)) {
            return $this->images[0]->imagePath;
        }
        return 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=800&q=80';
    }

    public function getTotalStock(): int
    {
        $sum = 0;
        foreach ($this->variants as $variant) {
            $sum += $variant->stock;
        }
        return $sum;
    }

    public function getAvailableSizes(): array
    {
        $sizes = [];
        foreach ($this->variants as $variant) {
            if (!in_array($variant->size, $sizes)) {
                $sizes[] = $variant->size;
            }
        }
        return $sizes;
    }

    public function getAvailableColors(): array
    {
        $colors = [];
        foreach ($this->variants as $variant) {
            $colors[$variant->color] = $variant->colorCode;
        }
        return $colors;
    }
}
