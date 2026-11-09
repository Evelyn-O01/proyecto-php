<?php

namespace App\Presentation;

class Router
{
    private array $routes = [];

    public function get(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    private function addRoute(string $method, string $path, array|callable $handler, array $middlewares): void
    {
        // Convert path like /product/{slug} into regex
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = "#^" . $pattern . "$#";

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public function dispatch(string $method, string $uri): void
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
                // Execute middlewares
                foreach ($route['middlewares'] as $mw) {
                    if (is_callable($mw)) {
                        $mw();
                    }
                }

                // Filter named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$class, $action] = $handler;
                    $controller = new $class();
                    $controller->$action($params);
                    return;
                } elseif (is_callable($handler)) {
                    $handler($params);
                    return;
                }
            }
        }

        // 404 Not Found
        http_response_code(404);
        echo self::renderView('store/404', ['title' => 'Página no encontrada | AURA']);
    }

    public static function renderView(string $view, array $data = [], string $layout = 'main'): string
    {
        extract($data);
        $viewFile = dirname(__DIR__, 2) . '/views/' . $view . '.php';
        
        if (!file_exists($viewFile)) {
            return "Error: Vista '{$view}' no encontrada en {$viewFile}";
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === 'none') {
            return $content;
        }

        $layoutFile = dirname(__DIR__, 2) . '/views/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            ob_start();
            require $layoutFile;
            return ob_get_clean();
        }

        return $content;
    }
}
