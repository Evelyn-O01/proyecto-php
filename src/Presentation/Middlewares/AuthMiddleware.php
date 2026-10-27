<?php

namespace App\Presentation\Middlewares;

class AuthMiddleware
{
    public static function handle(): void
    {
        if (!SessionManager::isAuthenticated()) {
            SessionManager::setFlash('error', 'Debes iniciar sesión para acceder a esta sección.');
            header('Location: /login');
            exit;
        }
    }
}
