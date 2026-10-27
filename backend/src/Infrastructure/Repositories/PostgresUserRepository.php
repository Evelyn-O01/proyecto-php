<?php

namespace App\Backend\Infrastructure\Repositories;

use App\Backend\Domain\Entities\User;
use App\Backend\Domain\Ports\UserRepositoryPort;
use PDO;

class PostgresUserRepository implements UserRepositoryPort
{
    public function __construct(private PDO $db)
    {
    }

    public function findById(string $id): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? User::fromArray($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(:email)");
        $stmt->execute(['email' => trim($email)]);
        $row = $stmt->fetch();
        return $row ? User::fromArray($row) : null;
    }

    public function save(User $user): User
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (id, name, email, password_hash, role, phone, is_active)
            VALUES (COALESCE(:id, uuid_generate_v4()), :name, :email, :password_hash, :role, :phone, :is_active)
            RETURNING id, created_at, updated_at
        ");
        $stmt->execute([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'password_hash' => $user->passwordHash,
            'role' => $user->role->value,
            'phone' => $user->phone,
            'is_active' => $user->isActive ? 1 : 0,
        ]);
        $row = $stmt->fetch();
        return new User(
            id: $row['id'],
            name: $user->name,
            email: $user->email,
            passwordHash: $user->passwordHash,
            role: $user->role,
            phone: $user->phone,
            isActive: $user->isActive,
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at']
        );
    }

    public function update(User $user): bool
    {
        $stmt = $this->db->prepare("
            UPDATE users SET
                name = :name,
                email = :email,
                role = :role,
                phone = :phone,
                is_active = :is_active,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'phone' => $user->phone,
            'is_active' => $user->isActive ? 1 : 0,
        ]);
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY created_at DESC");
        $rows = $stmt->fetchAll();
        return array_map(fn($r) => User::fromArray($r), $rows);
    }
}
