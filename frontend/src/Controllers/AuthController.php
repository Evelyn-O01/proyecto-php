<?php

namespace App\Frontend\Controllers;

use App\Frontend\Services\BackendClient;
use App\Frontend\Services\Session;

class AuthController
{
    private BackendClient $client;

    public function __construct()
    {
        $this->client = new BackendClient();
    }

    public function showLogin(): void
    {
        if (Session::isAuthenticated()) {
            $user = Session::getUser();
            $this->redirectByRole($user['role']);
            return;
        }

        echo StoreController::render('auth/login', ['title' => 'Iniciar Sesión | AURA Atelier']);
    }

    public function login(): void
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        try {
            $user = $this->client->post('/api/v1/auth/login', [
                'email' => $email,
                'password' => $password
            ]);

            Session::setUser($user);
            Session::flash('success', "Bienvenido de nuevo, {$user['name']}.");
            $this->redirectByRole($user['role']);
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
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
            $user = $this->client->post('/api/v1/auth/login', [
                'email' => $email,
                'password' => $pass
            ]);
            Session::setUser($user);
            Session::flash('success', "Acceso rápido como {$user['role_label']}.");
            $this->redirectByRole($user['role']);
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            header('Location: /login');
            exit;
        }
    }

    public function showRegister(): void
    {
        if (Session::isAuthenticated()) {
            header('Location: /');
            exit;
        }

        echo StoreController::render('auth/register', ['title' => 'Crear Cuenta | AURA Atelier']);
    }

    public function register(): void
    {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'CLIENT';
        $phone = $_POST['phone'] ?? null;

        try {
            $user = $this->client->post('/api/v1/auth/register', [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => $role,
                'phone' => $phone
            ]);
            Session::setUser($user);
            Session::flash('success', '¡Cuenta creada con éxito!');
            $this->redirectByRole($user['role']);
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            header('Location: /register');
            exit;
        }
    }

    public function logout(): void
    {
        Session::logout();
        Session::flash('info', 'Sesión cerrada correctamente.');
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
