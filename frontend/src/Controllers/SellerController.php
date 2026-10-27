<?php

namespace App\Frontend\Controllers;

use App\Frontend\Services\BackendClient;
use App\Frontend\Services\Session;

class SellerController
{
    private BackendClient $client;

    public function __construct()
    {
        $this->client = new BackendClient();
    }

    public function dashboard(): void
    {
        $user = Session::getUser();
        if (!$user || !Session::hasRole('SELLER', 'ADMIN')) {
            Session::flash('error', 'Acceso exclusivo para vendedores.');
            header('Location: /login');
            exit;
        }

        try {
            $products = $this->client->get('/api/v1/seller/' . $user['id'] . '/products');
            $orders = $this->client->get('/api/v1/seller/' . $user['id'] . '/sales');
        } catch (\Throwable $e) {
            $products = [];
            $orders = [];
        }

        $totalUnits = 0;
        $totalValuation = 0.0;
        foreach ($products as $p) {
            $variants = is_array($p) ? ($p['variants'] ?? []) : $p->variants;
            $basePrice = is_array($p) ? (float)($p['base_price'] ?? 0) : (float)$p->basePrice;
            foreach ($variants as $v) {
                $stock = is_array($v) ? (int)($v['stock'] ?? 0) : (int)$v->stock;
                $extraPrice = is_array($v) ? (float)($v['additional_price'] ?? 0) : (float)$v->additionalPrice;
                $totalUnits += $stock;
                $totalValuation += $stock * ($basePrice + $extraPrice);
            }
        }

        $data = [
            'title' => 'Panel de Vendedor | AURA Atelier',
            'user' => $user,
            'products' => $products,
            'orders' => $orders,
            'totalProducts' => count($products),
            'totalUnits' => $totalUnits,
            'totalValuation' => $totalValuation
        ];

        echo StoreController::render('seller/dashboard', $data, 'admin');
    }

    public function edit(array $params): void
    {
        $user = Session::getUser();
        if (!$user || !Session::hasRole('SELLER', 'ADMIN')) {
            Session::flash('error', 'Acceso exclusivo para vendedores.');
            header('Location: /login');
            exit;
        }

        $id = $params['id'] ?? '';
        try {
            $product = $this->client->get('/api/v1/products/' . $id);
            $categories = $this->client->get('/api/v1/categories');
        } catch (\Throwable $e) {
            Session::flash('error', 'Prenda no encontrada: ' . $e->getMessage());
            header('Location: /seller');
            exit;
        }

        $data = [
            'title' => 'Editar Prenda | AURA Atelier',
            'user' => $user,
            'product' => $product,
            'categories' => $categories
        ];

        echo StoreController::render('seller/product_edit', $data, 'admin');
    }

    public function update(array $params): void
    {
        $user = Session::getUser();
        if (!$user || !Session::hasRole('SELLER', 'ADMIN')) {
            header('Location: /login');
            exit;
        }

        $id = $params['id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 1);
        $description = trim($_POST['description'] ?? '');
        $basePrice = (float)($_POST['base_price'] ?? 0);
        $status = $_POST['status'] ?? 'ACTIVE';
        $variantStocks = $_POST['variant_stock'] ?? [];

        try {
            $this->client->post('/api/v1/seller/products/' . $id, [
                'seller_id' => $user['id'],
                'category_id' => $categoryId,
                'name' => $name,
                'description' => $description,
                'base_price' => $basePrice,
                'status' => $status,
                'variant_stock' => $variantStocks
            ]);

            Session::flash('success', 'Prenda actualizada correctamente.');
            header('Location: /seller');
            exit;
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            header('Location: /seller/product/edit/' . $id);
            exit;
        }
    }

    public function delete(array $params): void
    {
        $user = Session::getUser();
        if (!$user || !Session::hasRole('SELLER', 'ADMIN')) {
            header('Location: /login');
            exit;
        }

        $id = $params['id'] ?? '';
        try {
            $this->client->post('/api/v1/seller/products/delete/' . $id, [
                'seller_id' => $user['id']
            ]);
            Session::flash('success', 'Prenda eliminada del catálogo.');
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
        }

        header('Location: /seller');
        exit;
    }

    public function create(): void
    {
        $user = Session::getUser();
        if (!$user || !Session::hasRole('SELLER', 'ADMIN')) {
            header('Location: /login');
            exit;
        }

        try {
            $categories = $this->client->get('/api/v1/categories');
        } catch (\Throwable $e) {
            $categories = [];
        }

        $data = [
            'title' => 'Publicar Nueva Prenda | AURA Atelier',
            'categories' => $categories,
            'user' => $user,
        ];

        echo StoreController::render('seller/product_form', $data, 'admin');
    }

    public function store(): void
    {
        $user = Session::getUser();
        if (!$user || !Session::hasRole('SELLER', 'ADMIN')) {
            header('Location: /login');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 1);
        $description = trim($_POST['description'] ?? '');
        $basePrice = (float)($_POST['base_price'] ?? 0);

        // Procesar variantes
        $sizes = $_POST['variants_size'] ?? [];
        $colors = $_POST['variants_color'] ?? [];
        $colorCodes = $_POST['variants_color_code'] ?? [];
        $stocks = $_POST['variants_stock'] ?? [];
        $prices = $_POST['variants_price'] ?? [];

        $variantsData = [];
        for ($i = 0; $i < count($sizes); $i++) {
            if (!empty($sizes[$i]) && !empty($colors[$i])) {
                $variantsData[] = [
                    'size' => $sizes[$i],
                    'color' => $colors[$i],
                    'color_code' => $colorCodes[$i] ?? '#000000',
                    'stock' => (int)($stocks[$i] ?? 0),
                    'additional_price' => (float)($prices[$i] ?? 0.0),
                ];
            }
        }

        $imagesData = [];
        if (!empty($_POST['image_url'])) {
            $imagesData[] = trim($_POST['image_url']);
        }
        if (empty($imagesData)) {
            $imagesData[] = 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=800&q=80';
        }

        try {
            $this->client->post('/api/v1/seller/products', [
                'seller_id' => $user['id'],
                'category_id' => $categoryId,
                'name' => $name,
                'description' => $description,
                'base_price' => $basePrice,
                'variants' => $variantsData,
                'images' => $imagesData
            ]);

            Session::flash('success', 'Prenda publicada correctamente en el catálogo.');
            header('Location: /seller');
            exit;
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            header('Location: /seller/product/new');
            exit;
        }
    }

    public function sales(): void
    {
        $user = Session::getUser();
        if (!$user || !Session::hasRole('SELLER', 'ADMIN')) {
            header('Location: /login');
            exit;
        }

        try {
            $orders = $this->client->get('/api/v1/seller/' . $user['id'] . '/sales');
        } catch (\Throwable $e) {
            $orders = [];
        }

        $data = [
            'title' => 'Historial de Ventas | AURA Atelier',
            'user' => $user,
            'orders' => $orders,
        ];

        echo StoreController::render('seller/sales', $data, 'admin');
    }
}
