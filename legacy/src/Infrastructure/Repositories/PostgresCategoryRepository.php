<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Category;
use App\Domain\Ports\CategoryRepositoryPort;
use PDO;

class PostgresCategoryRepository implements CategoryRepositoryPort
{
    public function __construct(private PDO $db)
    {
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM categories ORDER BY id ASC");
        $rows = $stmt->fetchAll();
        return array_map(fn($r) => Category::fromArray($r), $rows);
    }

    public function findById(int $id): ?Category
    {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? Category::fromArray($row) : null;
    }

    public function findBySlug(string $slug): ?Category
    {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE slug = :slug");
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ? Category::fromArray($row) : null;
    }

    public function save(Category $category): Category
    {
        if ($category->id) {
            $stmt = $this->db->prepare("
                UPDATE categories SET name = :name, slug = :slug, description = :description, image_url = :image_url
                WHERE id = :id
            ");
            $stmt->execute([
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image_url' => $category->imageUrl,
            ]);
            return $category;
        }

        $stmt = $this->db->prepare("
            INSERT INTO categories (name, slug, description, image_url)
            VALUES (:name, :slug, :description, :image_url)
            RETURNING id, created_at
        ");
        $stmt->execute([
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'image_url' => $category->imageUrl,
        ]);
        $row = $stmt->fetch();
        return new Category(
            id: (int)$row['id'],
            name: $category->name,
            slug: $category->slug,
            description: $category->description,
            imageUrl: $category->imageUrl,
            createdAt: $row['created_at']
        );
    }
}
