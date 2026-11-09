<?php

namespace App\Frontend\Services;

use RuntimeException;
use Throwable;

require_once dirname(__DIR__, 3) . '/config/env.php';

class Session
{
    private const COOKIE_NAME = 'aura_browser_token';
    private const COOKIE_TTL = 2592000; // 30 días
    private const DEFAULT_LOCK_TTL = 1800; // 30 minutos
    private const DEFAULT_TOUCH_INTERVAL = 300; // 5 minutos

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    /**
     * Inicia la sesión del usuario y reserva la cuenta para este navegador.
     *
     * @throws RuntimeException si la cuenta ya está activa en otro navegador.
     */
    public static function setUser(array $userData): void
    {
        self::start();

        $userId = (string)($userData['id'] ?? '');
        if ($userId === '') {
            throw new RuntimeException('No se pudo identificar al usuario de la sesión.');
        }

        $previousUser = $_SESSION['user'] ?? null;
        $currentToken = self::currentToken();

        if ($previousUser && (string)($previousUser['id'] ?? '') !== $userId) {
            self::releaseActiveSession((string)($previousUser['id'] ?? ''), $currentToken);
        }

        $token = self::claimActiveSession($userId, $currentToken);

        $_SESSION['user'] = $userData;
        $_SESSION['auth_token'] = $token;
        $_SESSION['auth_last_seen'] = time();

        self::writeBrowserCookie($token);
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

    /**
     * Verifica que la cuenta siga reservada por este navegador.
     * Debe ejecutarse al inicio de cada petición autenticada.
     */
    public static function enforceActiveSession(): void
    {
        self::start();

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            return;
        }

        // El logout debe poder ejecutarse aunque la sesión haya sido reemplazada.
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if ($path === '/logout') {
            return;
        }

        $userId = (string)($user['id'] ?? '');
        if ($userId === '') {
            self::logout(false);
            return;
        }

        $token = self::currentToken();

        // Sesión creada antes de activar el bloqueo: reclamar un token.
        if ($token === null) {
            try {
                $token = self::claimActiveSession($userId, null);
                $_SESSION['auth_token'] = $token;
                $_SESSION['auth_last_seen'] = time();
                self::writeBrowserCookie($token);
            } catch (Throwable $e) {
                self::logout(false);
                self::flash('error', $e->getMessage());
                header('Location: /login');
                exit;
            }
            return;
        }

        // No escribir en disco en cada petición: solo cada cierto intervalo.
        $lastSeen = (int)($_SESSION['auth_last_seen'] ?? 0);
        if ($lastSeen > 0 && (time() - $lastSeen) < self::touchInterval()) {
            return;
        }

        try {
            $status = self::touchActiveSession($userId, $token);
        } catch (Throwable $e) {
            // Si el registro no se puede leer/escribir, no bloqueamos la tienda.
            error_log('No se pudo validar la sesion activa: ' . $e->getMessage());
            return;
        }

        if ($status === 'conflict') {
            self::logout(false);
            self::flash('error', 'Tu sesión se cerró porque esta cuenta se inició en otro navegador.');
            header('Location: /login');
            exit;
        }

        $_SESSION['auth_last_seen'] = time();
        self::writeBrowserCookie($token);
    }

    public static function logout(bool $release = true): void
    {
        self::start();

        $user = $_SESSION['user'] ?? null;
        $token = self::currentToken();

        if ($release && $user && $token) {
            self::releaseActiveSession((string)($user['id'] ?? ''), $token);
        }

        unset($_SESSION['user'], $_SESSION['auth_token'], $_SESSION['auth_last_seen']);
        self::clearBrowserCookie();
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

    /**
     * Reserva la cuenta para el token de este navegador.
     */
    private static function claimActiveSession(string $userId, ?string $token): string
    {
        $token = $token !== null && self::isValidToken($token) ? $token : self::generateToken();
        $now = time();
        $ttl = self::lockTtl();

        self::withRegistry(function (array &$registry) use ($userId, $token, $now, $ttl): void {
            self::pruneRegistry($registry, $now, $ttl);

            // Si este mismo navegador cambió de cuenta, liberar la anterior.
            foreach ($registry as $otherUserId => $entry) {
                if (
                    $otherUserId !== $userId &&
                    is_array($entry) &&
                    ($entry['token'] ?? '') === $token
                ) {
                    unset($registry[$otherUserId]);
                }
            }

            $existing = $registry[$userId] ?? null;
            if (is_array($existing) && ($existing['token'] ?? '') !== $token) {
                throw new RuntimeException(
                    "Esta cuenta ya tiene una sesión activa en otro navegador. " .
                    "Cierra sesión allí para poder ingresar aquí."
                );
            }

            $registry[$userId] = self::sessionEntry($token, $now);
        });

        return $token;
    }

    /**
     * Refresca la reserva de la cuenta para este navegador.
     *
     * @return string ok|reclaimed|conflict
     */
    private static function touchActiveSession(string $userId, string $token): string
    {
        $status = 'ok';
        $now = time();
        $ttl = self::lockTtl();

        self::withRegistry(function (array &$registry) use ($userId, $token, $now, $ttl, &$status): void {
            self::pruneRegistry($registry, $now, $ttl);

            $existing = $registry[$userId] ?? null;

            // La reserva expiró (o no existía): este navegador puede reclaimarla.
            if (!is_array($existing) || ($existing['token'] ?? '') === '') {
                $registry[$userId] = self::sessionEntry($token, $now);
                $status = 'reclaimed';
                return;
            }

            if (($existing['token'] ?? '') !== $token) {
                $status = 'conflict';
                return;
            }

            $registry[$userId] = self::sessionEntry($token, $now);
            $status = 'ok';
        });

        return $status;
    }

    private static function releaseActiveSession(string $userId, ?string $token): void
    {
        if ($userId === '' || $token === null) {
            return;
        }

        self::withRegistry(function (array &$registry) use ($userId, $token): void {
            if (
                isset($registry[$userId]) &&
                is_array($registry[$userId]) &&
                ($registry[$userId]['token'] ?? '') === $token
            ) {
                unset($registry[$userId]);
            }

            self::pruneRegistry($registry, time(), self::lockTtl());
        });
    }

    private static function sessionEntry(string $token, int $now): array
    {
        return [
            'token' => $token,
            'last_seen' => $now,
            'session_id' => session_id(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 200),
        ];
    }

    private static function pruneRegistry(array &$registry, int $now, int $ttl): void
    {
        foreach ($registry as $userId => $entry) {
            if (!is_array($entry)) {
                unset($registry[$userId]);
                continue;
            }

            $lastSeen = (int)($entry['last_seen'] ?? 0);
            if ($lastSeen <= 0 || ($lastSeen + $ttl) < $now) {
                unset($registry[$userId]);
            }
        }
    }

    /**
     * Lee y escribe el registro bajo un bloqueo exclusivo para evitar
     * que dos navegadores reclamen la misma cuenta a la vez.
     */
    private static function withRegistry(callable $callback): void
    {
        $file = self::registryFile();
        $dir = dirname($file);

        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('No se pudo crear el directorio de sesiones activas.');
        }

        $handle = @fopen($file, 'c+');
        if ($handle === false) {
            throw new RuntimeException('No se pudo abrir el registro de sesiones activas.');
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new RuntimeException('No se pudo bloquear el registro de sesiones activas.');
            }

            $raw = stream_get_contents($handle);
            $registry = $raw !== false && trim($raw) !== '' ? json_decode($raw, true) : [];
            if (!is_array($registry)) {
                $registry = [];
            }

            $callback($registry);

            ftruncate($handle, 0);
            rewind($handle);

            $json = json_encode($registry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                throw new RuntimeException('No se pudo serializar el registro de sesiones activas.');
            }

            $written = fwrite($handle, $json);
            if ($written === false || $written !== strlen($json)) {
                throw new RuntimeException('No se pudo guardar el registro de sesiones activas.');
            }

            fflush($handle);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private static function registryFile(): string
    {
        $configured = getenv('SESSION_REGISTRY_FILE');
        if (is_string($configured) && trim($configured) !== '') {
            return $configured;
        }

        $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        if (!is_dir($dir) || !is_writable($dir)) {
            $dir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'aura_atelier_sessions';
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
        }

        return $dir . DIRECTORY_SEPARATOR . 'active_sessions.json';
    }

    private static function currentToken(): ?string
    {
        $sessionToken = $_SESSION['auth_token'] ?? null;
        if (self::isValidToken($sessionToken)) {
            return $sessionToken;
        }

        $cookieToken = $_COOKIE[self::COOKIE_NAME] ?? null;
        if (self::isValidToken($cookieToken)) {
            return $cookieToken;
        }

        return null;
    }

    private static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    private static function isValidToken(mixed $token): bool
    {
        return is_string($token) && preg_match('/^[a-f0-9]{64}$/', $token) === 1;
    }

    private static function writeBrowserCookie(string $token): void
    {
        $_COOKIE[self::COOKIE_NAME] = $token;

        if (headers_sent()) {
            return;
        }

        setcookie(self::COOKIE_NAME, $token, [
            'expires' => time() + self::COOKIE_TTL,
            'path' => '/',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function clearBrowserCookie(): void
    {
        unset($_COOKIE[self::COOKIE_NAME]);

        if (headers_sent()) {
            return;
        }

        setcookie(self::COOKIE_NAME, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function isHttps(): bool
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off';
    }

    private static function lockTtl(): int
    {
        $configured = (int)(getenv('SESSION_LOCK_TTL') ?: 0);
        return $configured > 0 ? max(60, $configured) : self::DEFAULT_LOCK_TTL;
    }

    private static function touchInterval(): int
    {
        $configured = (int)(getenv('SESSION_TOUCH_INTERVAL') ?: 0);
        $interval = $configured > 0 ? $configured : self::DEFAULT_TOUCH_INTERVAL;
        return max(30, min($interval, self::lockTtl()));
    }
}
