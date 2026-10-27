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
            foreach ($p['variants'] as $v) {
                $totalUnits += $v['stock'];
                $totalValuation += $v['stock'] * ($p['base_price'] + $v['additional_price']);
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
