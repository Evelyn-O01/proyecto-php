<?php

namespace App\Domain\Entities;

class ProductVariant
{
    public function __construct(
        public readonly ?string $id,
        public string $productId,
        public string $size,
        public string $color,
        public ?string $colorCode = '#000000',
        public string $sku = '',
        public int $stock = 0,
        public float $additionalPrice = 0.0,
        public ?string $createdAt = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            productId: $data['product_id'],
            size: $data['size'],
            color: $data['color'],
            colorCode: $data['color_code'] ?? '#000000',
            sku: $data['sku'] ?? '',
            stock: (int)($data['stock'] ?? 0),
            additionalPrice: (float)($data['additional_price'] ?? 0.0),
            createdAt: $data['created_at'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->productId,
            'size' => $this->size,
            'color' => $this->color,
            'color_code' => $this->colorCode,
            'sku' => $this->sku,
            'stock' => $this->stock,
            'additional_price' => $this->additionalPrice,
            'created_at' => $this->createdAt,
        ];
    }

    public function inStock(): bool
    {
        return $this->stock > 0;
    }
}
