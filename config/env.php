<?php

declare(strict_types=1);

/**
 * Cargador minimalista de variables de entorno (.env).
 *
 * No requiere dependencias externas. Las variables ya definidas en el
 * entorno real tienen prioridad sobre las del archivo .env.
 */

if (!function_exists('project_env_path')) {
    function project_env_path(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
    }
}

if (!function_exists('load_project_env')) {
    function load_project_env(?string $path = null): void
    {
        static $loaded = false;

        if ($loaded) {
            return;
        }

        $loaded = true;
        $path = $path ?: project_env_path();

        // Nota: en Windows, is_readable() puede devolver false para rutas UNC
        // (\\wsl.localhost\...) aunque file() si pueda leer el archivo.
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_starts_with($line, 'export ')) {
                $line = trim(substr($line, 7));
            }

            $separator = strpos($line, '=');
            if ($separator === false || $separator === 0) {
                continue;
            }

            $key = trim(substr($line, 0, $separator));
            $value = trim(substr($line, $separator + 1));

            if ($key === '') {
                continue;
            }

            if (strlen($value) >= 2) {
                $first = $value[0];
                $last = $value[strlen($value) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            // El entorno real siempre gana.
            if (getenv($key) === false) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

if (!function_exists('env_value')) {
    function env_value(string $key, ?string $default = null): ?string
    {
        $value = getenv($key);

        return $value !== false ? $value : $default;
    }
}

load_project_env();
