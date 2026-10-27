<?php

namespace App\Presentation\Middlewares;

class RoleMiddleware
{
    public static function requireRole(string ...$roles): void
    {
        AuthMiddleware::handle();

        if (!SessionManager::hasRole(...$roles)) {
            SessionManager::setFlash('error', 'No tienes los permisos requeridos para acceder a este módulo.');
            header('Location: /');
            exit;
        }
    }
}
