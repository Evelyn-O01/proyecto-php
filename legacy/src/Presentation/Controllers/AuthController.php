<?php

namespace App\Presentation\Controllers;

use App\Application\UseCases\Auth\AuthenticateUserUseCase;
use App\Application\UseCases\Auth\RegisterUserUseCase;
use App\Infrastructure\Database\PostgresConnection;
use App\Infrastructure\Repositories\PostgresUserRepository;
use App\Presentation\Middlewares\SessionManager;
use App\Presentation\Router;

class AuthController
{
    private AuthenticateUserUseCase $authUseCase;
    private RegisterUserUseCase $registerUseCase;

    public function __construct()
    {
        $pdo = PostgresConnection::getConnection();
        $userRepo = new PostgresUserRepository($pdo);
        $this->authUseCase = new AuthenticateUserUseCase($userRepo);
        $this->registerUseCase = new RegisterUserUseCase($userRepo);
    }

    public function showLogin(): void
    {
        if (SessionManager::isAuthenticated()) {
            $user = SessionManager::getUser();
            $this->redirectByRole($user['role']);
            return;
        }

        echo Router::renderView('auth/login', ['title' => 'Iniciar Sesión | AURA Atelier'], 'main');
    }

    public function login(): void
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        try {
            $user = $this->authUseCase->execute($email, $password);
            SessionManager::setUser($user);
            SessionManager::setFlash('success', "Bienvenido de nuevo, {$user->name}.");
            $this->redirectByRole($user->role->value);
        } catch (\Throwable $e) {
            SessionManager::setFlash('error', $e->getMessage());
            header('Location: /login');
            exit;
        }
    }

    public function quickLogin(array $params): void
    {
        $role = strtoupper($params['role'] ?? '');
        $email = match ($role) {
            'ADMIN' => 'admin@tiendaropa.com',
            'SELLER' => 'vendedor@tiendaropa.com',
            default => 'cliente@tiendaropa.com',
        };
        $pass = match ($role) {
            'ADMIN' => 'admin123',
            'SELLER' => 'seller123',
            default => 'client123',
        };

        try {
            $user = $this->authUseCase->execute($email, $pass);
            SessionManager::setUser($user);
            SessionManager::setFlash('success', "Acceso rápido concedido como {$user->role->label()}.");
            $this->redirectByRole($user->role->value);
        } catch (\Throwable $e) {
            SessionManager::setFlash('error', $e->getMessage());
            header('Location: /login');
            exit;
        }
    }

    public function showRegister(): void
    {
        if (SessionManager::isAuthenticated()) {
            header('Location: /');
            exit;
        }

        echo Router::renderView('auth/register', ['title' => 'Crear Cuenta | AURA Atelier'], 'main');
    }

    public function register(): void
    {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'CLIENT';
        $phone = $_POST['phone'] ?? null;

        try {
            $user = $this->registerUseCase->execute($name, $email, $password, $role, $phone);
            SessionManager::setUser($user);
            SessionManager::setFlash('success', '¡Cuenta creada con éxito!');
            $this->redirectByRole($user->role->value);
        } catch (\Throwable $e) {
            SessionManager::setFlash('error', $e->getMessage());
            header('Location: /register');
            exit;
        }
    }

    public function logout(): void
    {
        SessionManager::logout();
        SessionManager::setFlash('info', 'Has cerrado sesión correctamente.');
        header('Location: /');
        exit;
    }

    private function redirectByRole(string $role): void
    {
        match ($role) {
            'ADMIN' => header('Location: /admin'),
            'SELLER' => header('Location: /seller'),
            default => header('Location: /catalog'),
        };
        exit;
    }
}
