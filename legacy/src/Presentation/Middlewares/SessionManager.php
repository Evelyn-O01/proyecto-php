<?php

namespace App\Presentation\Middlewares;

use App\Domain\Entities\User;

class SessionManager
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function setUser(User $user): void
    {
        self::start();
        $_SESSION['user'] = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'phone' => $user->phone
        ];
    }

    public static function getUser(): ?array
    {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    public static function isAuthenticated(): bool
    {
        return self::getUser() !== null;
    }

    public static function hasRole(string ...$roles): bool
    {
        $user = self::getUser();
        if (!$user) return false;
        return in_array($user['role'], $roles, true);
    }

    public static function logout(): void
    {
        self::start();
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }

    public static function setFlash(string $type, string $message): void
    {
        self::start();
        $_SESSION['flash'][$type][] = $message;
    }

    public static function getFlash(string $type): array
    {
        self::start();
        $messages = $_SESSION['flash'][$type] ?? [];
        unset($_SESSION['flash'][$type]);
        return $messages;
    }

    public static function hasFlash(string $type): bool
    {
        self::start();
        return !empty($_SESSION['flash'][$type]);
    }
}
