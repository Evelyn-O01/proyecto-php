<?php

namespace App\Application\UseCases\Auth;

use App\Domain\Entities\User;
use App\Domain\Ports\UserRepositoryPort;
use App\Domain\ValueObjects\Role;
use RuntimeException;

class RegisterUserUseCase
{
    public function __construct(private UserRepositoryPort $userRepo)
    {
    }

    public function execute(string $name, string $email, string $password, string $role = 'CLIENT', ?string $phone = null): User
    {
        $email = trim(strtolower($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException("El formato del correo electrónico es inválido.");
        }

        if (strlen($password) < 6) {
            throw new RuntimeException("La contraseña debe contener al menos 6 caracteres.");
        }

        if ($this->userRepo->findByEmail($email)) {
            throw new RuntimeException("Ya existe una cuenta registrada con este correo electrónico.");
        }

        $validRole = match (strtoupper($role)) {
            'SELLER' => Role::SELLER,
            default => Role::CLIENT,
        };

        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $user = new User(
            id: null,
            name: trim($name),
            email: $email,
            passwordHash: $passwordHash,
            role: $validRole,
            phone: $phone,
            isActive: true
        );

        return $this->userRepo->save($user);
    }
}
