<?php

namespace App\Backend\Endpoints;

use App\Backend\Controllers\AuthController;

class AuthEndpoints
{
    private AuthController $controller;

    public function __construct()
    {
        $this->controller = new AuthController();
    }

    public function login(): void
    {
        $body = ApiRouter::getJsonBody();
        $email = $body['email'] ?? '';
        $password = $body['password'] ?? '';

        try {
            $user = $this->controller->authenticate($email, $password);
            ApiRouter::json(['success' => true, 'data' => $user]);
        } catch (\Throwable $e) {
            ApiRouter::json(['success' => false, 'error' => $e->getMessage()], 401);
        }
    }

    public function register(): void
    {
        $body = ApiRouter::getJsonBody();
        $name = $body['name'] ?? '';
        $email = $body['email'] ?? '';
        $password = $body['password'] ?? '';
        $role = $body['role'] ?? 'CLIENT';
        $phone = $body['phone'] ?? null;

        try {
            $user = $this->controller->register($name, $email, $password, $role, $phone);
            ApiRouter::json(['success' => true, 'data' => $user], 201);
        } catch (\Throwable $e) {
            ApiRouter::json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function getUser(array $params): void
    {
        $id = $params['id'] ?? '';
        $user = $this->controller->getUser($id);
        if ($user) {
            ApiRouter::json(['success' => true, 'data' => $user]);
        } else {
            ApiRouter::json(['success' => false, 'error' => 'Usuario no encontrado'], 404);
        }
    }
}
