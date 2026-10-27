<?php

namespace App\Backend\Domain\Entities;

class Category
{
    public function __construct(
        public readonly ?int $id,
        public string $name,
        public string $slug,
        public ?string $description = null,
        public ?string $imageUrl = null,
        public ?string $createdAt = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
            imageUrl: $data['image_url'] ?? null,
            createdAt: $data['created_at'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image_url' => $this->imageUrl,
            'created_at' => $this->createdAt,
        ];
    }
}
