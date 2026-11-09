<?php

namespace App\Frontend\Services;

use RuntimeException;

class BackendClient
{
    private string $baseUrl;
    private int $timeout;

    public function __construct(?string $baseUrl = null, ?int $timeout = null)
    {
        $config = require dirname(__DIR__, 2) . '/config/api.php';
        $this->baseUrl = rtrim($baseUrl ?: $config['base_url'], '/');
        $this->timeout = $timeout ?? (int)($config['timeout'] ?? 10);
    }

    public function get(string $endpoint, array $query = []): array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $this->request('GET', $url);
    }

    public function post(string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        return $this->request('POST', $url, $data);
    }

    private function request(string $method, string $url, ?array $data = null): array
    {
        // Si cURL está disponible, intentar llamada HTTP
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);

            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data ?? []));
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if (!$curlError && $response) {
                $decoded = json_decode($response, true);
                if (is_array($decoded)) {
                    if ($httpCode >= 400 || ($decoded['success'] ?? true) === false) {
                        throw new RuntimeException($decoded['error'] ?? "Error HTTP {$httpCode} desde Backend");
                    }
                    return $decoded['data'] ?? $decoded;
                }
            }
        }

        // Bridge directo si el servidor remoto de desarrollo no está escuchando
        return $this->directBackendBridge($method, $url, $data);
    }

    /**
     * Fallback interno de alta velocidad para entornos locales o pruebas unitarias
     */
    private function directBackendBridge(string $method, string $url, ?array $data = null): array
    {
        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '/';
        $query = [];
        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $query);
        }

        // 1. Catálogo
        if ($path === '/api/v1/products') {
            $ctrl = new \App\Backend\Controllers\ProductController();
            $page = max(1, (int)($query['page'] ?? 1));
            $perPage = max(1, (int)($query['per_page'] ?? 12));
            return $ctrl->list($query, $page, $perPage);
        }

        if (preg_match('#^/api/v1/products/([^/]+)$#', $path, $m)) {
            $ctrl = new \App\Backend\Controllers\ProductController();
            return $ctrl->getDetail($m[1]);
        }

        if ($path === '/api/v1/categories') {
            $ctrl = new \App\Backend\Controllers\ProductController();
            return $ctrl->getCategories();
        }

        // 2. Auth
        if ($path === '/api/v1/auth/login') {
            $ctrl = new \App\Backend\Controllers\AuthController();
            return $ctrl->authenticate($data['email'] ?? '', $data['password'] ?? '');
        }

        if ($path === '/api/v1/auth/register') {
            $ctrl = new \App\Backend\Controllers\AuthController();
            return $ctrl->register(
                $data['name'] ?? '',
                $data['email'] ?? '',
                $data['password'] ?? '',
                $data['role'] ?? 'CLIENT',
                $data['phone'] ?? null
            );
        }

        // 3. Checkout
        if ($path === '/api/v1/checkout/process') {
            $ctrl = new \App\Backend\Controllers\OrderController();
            return $ctrl->processCheckout(
                $data['cart_items'] ?? [],
                $data['customer'] ?? [],
                $data['shipping_address'] ?? [],
                $data['payment'] ?? []
            );
        }

        if (preg_match('#^/api/v1/orders/([^/]+)$#', $path, $m)) {
            $ctrl = new \App\Backend\Controllers\OrderController();
            $res = $ctrl->getByNumber($m[1]);
            if (!$res) throw new RuntimeException("Pedido no encontrado.");
            return $res;
        }

        if (preg_match('#^/api/v1/customer/([^/]+)/orders$#', $path, $m)) {
            $ctrl = new \App\Backend\Controllers\OrderController();
            return $ctrl->getByCustomer($m[1]);
        }

        // 4. Seller
        if (preg_match('#^/api/v1/seller/([^/]+)/products$#', $path, $m)) {
            $ctrl = new \App\Backend\Controllers\ProductController();
            return $ctrl->getBySeller($m[1]);
        }

        if (preg_match('#^/api/v1/seller/([^/]+)/sales$#', $path, $m)) {
            $ctrl = new \App\Backend\Controllers\OrderController();
            return $ctrl->getBySeller($m[1]);
        }

        if (preg_match('#^/api/v1/seller/products/delete/([^/]+)$#', $path, $m)) {
            $ctrl = new \App\Backend\Controllers\ProductController();
            $ok = $ctrl->deleteProduct($m[1], $data['seller_id'] ?? '');
            return ['success' => $ok];
        }

        if (preg_match('#^/api/v1/seller/products/([^/]+)$#', $path, $m)) {
            $ctrl = new \App\Backend\Controllers\ProductController();
            $prod = $ctrl->updateProduct(
                $m[1],
                $data['seller_id'] ?? '',
                (int)($data['category_id'] ?? 1),
                $data['name'] ?? '',
                $data['description'] ?? '',
                (float)($data['base_price'] ?? 0),
                $data['status'] ?? 'ACTIVE',
                $data['variant_stock'] ?? []
            );
            return ['id' => $prod->id];
        }

        if ($path === '/api/v1/seller/products') {
            $ctrl = new \App\Backend\Controllers\ProductController();
            $prod = $ctrl->createProduct(
                $data['seller_id'] ?? '',
                (int)($data['category_id'] ?? 1),
                $data['name'] ?? '',
                $data['description'] ?? '',
                (float)($data['base_price'] ?? 0),
                $data['variants'] ?? [],
                $data['images'] ?? []
            );
            return ['id' => $prod->id, 'slug' => $prod->slug];
        }

        // 5. Admin
        if ($path === '/api/v1/admin/users') {
            $ctrl = new \App\Backend\Controllers\AdminController();
            return $ctrl->getUsers();
        }

        if ($path === '/api/v1/admin/users/role') {
            $ctrl = new \App\Backend\Controllers\AdminController();
            $ok = $ctrl->updateUserRole($data['user_id'] ?? '', $data['role'] ?? 'CLIENT');
            return ['success' => $ok];
        }

        if ($path === '/api/v1/admin/categories') {
            $ctrl = new \App\Backend\Controllers\AdminController();
            return $ctrl->createCategory($data['name'] ?? '', $data['description'] ?? '', $data['image_url'] ?? null);
        }

        // 6. Reports Data
        if ($path === '/api/v1/reports/sales/data') {
            $ctrl = new \App\Backend\Controllers\ReportController();
            return $ctrl->getSalesReport($query['start_date'] ?? null, $query['end_date'] ?? null, !empty($query['category_id']) ? (int)$query['category_id'] : null);
        }

        if ($path === '/api/v1/reports/inventory/data') {
            $ctrl = new \App\Backend\Controllers\ReportController();
            return $ctrl->getInventoryReport(!empty($query['category_id']) ? (int)$query['category_id'] : null, (int)($query['threshold'] ?? 10));
        }

        if ($path === '/api/v1/reports/sellers/data') {
            $ctrl = new \App\Backend\Controllers\ReportController();
            return $ctrl->getSellerReport($query['start_date'] ?? null, $query['end_date'] ?? null);
        }

        throw new RuntimeException("Endpoint '{$path}' no implementado en bridge.");
    }
}
