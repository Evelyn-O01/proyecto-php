<?php

namespace App\Domain\Entities;

class ProductImage
{
    public function __construct(
        public readonly ?string $id,
        public string $productId,
        public string $imagePath,
        public bool $isPrimary = false,
        public int $sortOrder = 0,
        public ?string $createdAt = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            productId: $data['product_id'],
            imagePath: $data['image_path'],
            isPrimary: (bool)($data['is_primary'] ?? false),
            sortOrder: (int)($data['sort_order'] ?? 0),
            createdAt: $data['created_at'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->productId,
            'image_path' => $this->imagePath,
            'is_primary' => $this->isPrimary,
            'sort_order' => $this->sortOrder,
            'created_at' => $this->createdAt,
        ];
    }
}
