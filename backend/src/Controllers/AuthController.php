<?php

namespace App\Backend\Controllers;

use App\Backend\Domain\Entities\User;
use App\Backend\Domain\Ports\UserRepositoryPort;
use App\Backend\Domain\ValueObjects\Role;
use App\Backend\Infrastructure\Database\PostgresConnection;
use App\Backend\Infrastructure\Repositories\PostgresUserRepository;
use RuntimeException;

class AuthController
{
    private UserRepositoryPort $userRepo;

    public function __construct(?UserRepositoryPort $userRepo = null)
    {
        $pdo = PostgresConnection::getConnection();
        $this->userRepo = $userRepo ?: new PostgresUserRepository($pdo);
    }

    public function authenticate(string $email, string $password): array
    {
        $user = $this->userRepo->findByEmail(trim($email));
        if (!$user) {
            throw new RuntimeException("Credenciales incorrectas.");
        }

        if (!$user->isActive) {
            throw new RuntimeException("Esta cuenta de usuario se encuentra inactiva.");
        }

        if (!password_verify($password, $user->passwordHash)) {
            throw new RuntimeException("Credenciales incorrectas.");
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'role_label' => $user->role->label(),
            'phone' => $user->phone
        ];
    }

    public function register(string $name, string $email, string $password, string $role = 'CLIENT', ?string $phone = null): array
    {
        $email = trim(strtolower($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException("El correo electrónico no es válido.");
        }

        if (strlen($password) < 6) {
            throw new RuntimeException("La contraseña debe tener mínimo 6 caracteres.");
        }

        if ($this->userRepo->findByEmail($email)) {
            throw new RuntimeException("El correo electrónico ya está registrado.");
        }

        $validRole = match (strtoupper($role)) {
            'SELLER' => Role::SELLER,
            default => Role::CLIENT,
        };

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $user = new User(
            id: null,
            name: trim($name),
            email: $email,
            passwordHash: $hash,
            role: $validRole,
            phone: $phone,
            isActive: true
        );

        $saved = $this->userRepo->save($user);

        return [
            'id' => $saved->id,
            'name' => $saved->name,
            'email' => $saved->email,
            'role' => $saved->role->value,
            'role_label' => $saved->role->label(),
            'phone' => $saved->phone
        ];
    }

    public function getUser(string $id): ?array
    {
        $user = $this->userRepo->findById($id);
        if (!$user) return null;
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'phone' => $user->phone
        ];
    }
}
