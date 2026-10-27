<?php

namespace App\Frontend\Services;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    public static function setUser(array $userData): void
    {
        self::start();
        $_SESSION['user'] = $userData;
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
        $u = self::getUser();
        if (!$u) return false;
        return in_array($u['role'], $roles, true);
    }

    public static function logout(): void
    {
        self::start();
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }

    public static function flash(string $type, string $message): void
    {
        self::start();
        $_SESSION['flash'][$type][] = $message;
    }

    public static function getFlash(string $type): array
    {
        self::start();
        $msgs = $_SESSION['flash'][$type] ?? [];
        unset($_SESSION['flash'][$type]);
        return $msgs;
    }
}
