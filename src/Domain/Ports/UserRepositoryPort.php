<?php

namespace App\Domain\Ports;

use App\Domain\Entities\User;

interface UserRepositoryPort
{
    public function findById(string $id): ?User;
    public function findByEmail(string $email): ?User;
    public function save(User $user): User;
    public function update(User $user): bool;
    /**
     * @return User[]
     */
    public function findAll(): array;
}
