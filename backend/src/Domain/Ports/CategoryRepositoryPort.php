<?php

namespace App\Backend\Domain\Ports;

use App\Backend\Domain\Entities\Category;

interface CategoryRepositoryPort
{
    /**
     * @return Category[]
     */
    public function findAll(): array;
    public function findById(int $id): ?Category;
    public function findBySlug(string $slug): ?Category;
    public function save(Category $category): Category;
}
