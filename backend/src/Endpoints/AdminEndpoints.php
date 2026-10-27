<?php

namespace App\Backend\Endpoints;

use App\Backend\Controllers\AdminController;

class AdminEndpoints
{
    private AdminController $controller;

    public function __construct()
    {
        $this->controller = new AdminController();
    }

    public function users(): void
    {
        $users = $this->controller->getUsers();
        ApiRouter::json(['success' => true, 'data' => $users]);
    }

    public function updateRole(): void
    {
        $body = ApiRouter::getJsonBody();
        $userId = $body['user_id'] ?? '';
        $role = $body['role'] ?? 'CLIENT';

        $success = $this->controller->updateUserRole($userId, $role);
        ApiRouter::json(['success' => $success]);
    }

    public function createCategory(): void
    {
        $body = ApiRouter::getJsonBody();
        $name = $body['name'] ?? '';
        $desc = $body['description'] ?? '';
        $img = $body['image_url'] ?? null;

        $cat = $this->controller->createCategory($name, $desc, $img);
        ApiRouter::json(['success' => true, 'data' => $cat], 201);
    }
}
