<?php

namespace App\Backend\Controllers;

use App\Backend\Domain\Entities\Category;
use App\Backend\Domain\Ports\CategoryRepositoryPort;
use App\Backend\Domain\Ports\UserRepositoryPort;
use App\Backend\Domain\ValueObjects\Role;
use App\Backend\Infrastructure\Database\PostgresConnection;
use App\Backend\Infrastructure\Repositories\PostgresCategoryRepository;
use App\Backend\Infrastructure\Repositories\PostgresUserRepository;

class AdminController
{
    private UserRepositoryPort $userRepo;
    private CategoryRepositoryPort $categoryRepo;

    public function __construct(
        ?UserRepositoryPort $userRepo = null,
        ?CategoryRepositoryPort $categoryRepo = null
    ) {
        $pdo = PostgresConnection::getConnection();
        $this->userRepo = $userRepo ?: new PostgresUserRepository($pdo);
        $this->categoryRepo = $categoryRepo ?: new PostgresCategoryRepository($pdo);
    }

    public function getUsers(): array
    {
        $users = $this->userRepo->findAll();
        return array_map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role->value,
                'role_label' => $u->role->label(),
                'phone' => $u->phone,
                'is_active' => $u->isActive,
                'created_at' => $u->createdAt,
            ];
        }, $users);
    }

    public function updateUserRole(string $userId, string $roleStr): bool
    {
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return false;
        }

        $user->role = Role::from(strtoupper($roleStr));
        return $this->userRepo->update($user);
    }

    public function createCategory(string $name, string $description, ?string $imageUrl = null): array
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        $category = new Category(
            id: null,
            name: trim($name),
            slug: $slug,
            description: trim($description),
            imageUrl: $imageUrl
        );
        $saved = $this->categoryRepo->save($category);
        return $saved->toArray();
    }
}
