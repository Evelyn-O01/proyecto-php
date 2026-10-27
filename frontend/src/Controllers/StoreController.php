<?php

namespace App\Frontend\Controllers;

use App\Frontend\Services\BackendClient;

class StoreController
{
    private BackendClient $client;

    public function __construct()
    {
        $this->client = new BackendClient();
    }

    public function catalog(): void
    {
        $filters = [
            'category_id' => $_GET['category_id'] ?? null,
            'category' => $_GET['category'] ?? null,
            'size' => $_GET['size'] ?? null,
            'color' => $_GET['color'] ?? null,
            'max_price' => $_GET['max_price'] ?? null,
            'q' => $_GET['q'] ?? null,
            'page' => $_GET['page'] ?? 1
        ];
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');

        try {
            $data = $this->client->get('/api/v1/products', $filters);
        } catch (\Throwable $e) {
            $data = [
                'products' => [],
                'total' => 0,
                'page' => 1,
                'total_pages' => 1,
                'categories' => [],
                'available_sizes' => [],
                'available_colors' => [],
                'min_price' => 0,
                'max_price' => 500
            ];
        }

        $data['title'] = 'Colección Exclusiva | AURA Atelier';
        $data['current_filters'] = $filters;

        echo self::render('store/catalog', $data);
    }

    public function detail(array $params): void
    {
        $slug = $params['slug'] ?? '';
        try {
            $product = $this->client->get('/api/v1/products/' . $slug);
            $data = [
                'title' => "{$product['name']} | AURA Atelier",
                'product' => $product
            ];
            echo self::render('store/product_detail', $data);
        } catch (\Throwable $e) {
            http_response_code(404);
            echo self::render('store/404', ['title' => 'Prenda no encontrada']);
        }
    }

    public static function render(string $view, array $data = [], string $layout = 'main'): string
    {
        extract($data);
        $file = dirname(__DIR__) . '/Views/' . ltrim($view, '/') . '.php';
        if (!file_exists($file)) {
            return "Error: Vista '{$view}' no encontrada en {$file}";
        }

        ob_start();
        require $file;
        $content = ob_get_clean();

        if ($layout === 'none') {
            return $content;
        }

        $layoutFile = dirname(__DIR__) . '/Views/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            ob_start();
            require $layoutFile;
            return ob_get_clean();
        }

        return $content;
    }
}
