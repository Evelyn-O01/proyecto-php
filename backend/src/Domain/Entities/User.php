<?php

namespace App\Backend\Domain\Entities;

use App\Backend\Domain\ValueObjects\Role;

class User
{
    public function __construct(
        public readonly ?string $id,
        public string $name,
        public string $email,
        public string $passwordHash,
        public Role $role,
        public ?string $phone = null,
        public bool $isActive = true,
        public ?string $createdAt = null,
        public ?string $updatedAt = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            email: $data['email'],
            passwordHash: $data['password_hash'],
            role: Role::from($data['role']),
            phone: $data['phone'] ?? null,
            isActive: (bool)($data['is_active'] ?? true),
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'password_hash' => $this->passwordHash,
            'role' => $this->role->value,
            'phone' => $this->phone,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::ADMIN;
    }

    public function isSeller(): bool
    {
        return $this->role === Role::SELLER;
    }

    public function isClient(): bool
    {
        return $this->role === Role::CLIENT;
    }
}
