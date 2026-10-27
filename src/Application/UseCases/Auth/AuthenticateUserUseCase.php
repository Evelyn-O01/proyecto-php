<?php

namespace App\Application\UseCases\Auth;

use App\Domain\Entities\User;
use App\Domain\Ports\UserRepositoryPort;
use RuntimeException;

class AuthenticateUserUseCase
{
    public function __construct(private UserRepositoryPort $userRepo)
    {
    }

    public function execute(string $email, string $password): User
    {
        $user = $this->userRepo->findByEmail($email);
        if (!$user) {
            throw new RuntimeException("Credenciales incorrectas.");
        }

        if (!$user->isActive) {
            throw new RuntimeException("Tu cuenta se encuentra inactiva. Contacta al administrador.");
        }

        if (!password_verify($password, $user->passwordHash)) {
            throw new RuntimeException("Credenciales incorrectas.");
        }

        return $user;
    }
}
