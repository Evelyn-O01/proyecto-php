<?php

namespace App\Backend\Endpoints;

class ApiRouter
{
    private array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable|array $handler): void
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = "#^" . $pattern . "$#";

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): bool
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $handler = $route['handler'];

                try {
                    if (is_array($handler)) {
                        [$class, $action] = $handler;
                        $instance = new $class();
                        $instance->$action($params);
                    } else {
                        $handler($params);
                    }
                } catch (\Throwable $e) {
                    self::json([
                        'success' => false,
                        'error' => $e->getMessage()
                    ], 400);
                }
                return true;
            }
        }

        return false;
    }

    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function getJsonBody(): array
    {
        $input = file_get_contents('php://input');
        if (!empty($input)) {
            $data = json_decode($input, true);
            if (is_array($data)) {
                return $data;
            }
        }
        return $_POST;
    }
}
