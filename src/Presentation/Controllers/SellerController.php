<?php

namespace App\Presentation\Controllers;

use App\Application\UseCases\Seller\ManageProductUseCase;
use App\Domain\Entities\ProductVariant;
use App\Domain\Ports\ProductRepositoryPort;
use App\Infrastructure\Database\PostgresConnection;
use App\Infrastructure\Repositories\PostgresCategoryRepository;
use App\Infrastructure\Repositories\PostgresOrderRepository;
use App\Infrastructure\Repositories\PostgresProductRepository;
use App\Infrastructure\Storage\LocalStorageAdapter;
use App\Presentation\Middlewares\RoleMiddleware;
use App\Presentation\Middlewares\SessionManager;
use App\Presentation\Router;

class SellerController
{
    private PostgresProductRepository $productRepo;
    private PostgresCategoryRepository $categoryRepo;
    private PostgresOrderRepository $orderRepo;
    private ManageProductUseCase $manageProductUseCase;
    private LocalStorageAdapter $storage;

    public function __construct()
    {
        RoleMiddleware::requireRole('SELLER', 'ADMIN');
        $pdo = PostgresConnection::getConnection();
        $this->productRepo = new PostgresProductRepository($pdo);
        $this->categoryRepo = new PostgresCategoryRepository($pdo);
        $this->orderRepo = new PostgresOrderRepository($pdo);
        $this->storage = new LocalStorageAdapter();
        $this->manageProductUseCase = new ManageProductUseCase($this->productRepo, $this->storage);
    }

    public function index(): void
    {
        $user = SessionManager::getUser();
        $products = $this->productRepo->findBySellerId($user['id']);
        $orders = $this->orderRepo->findBySellerId($user['id']);

        $totalUnits = 0;
        $totalValuation = 0.0;
        foreach ($products as $p) {
            foreach ($p->variants as $v) {
                $totalUnits += $v->stock;
                $totalValuation += $v->stock * ($p->basePrice + $v->additionalPrice);
            }
        }

        $data = [
            'title' => 'Panel de Vendedor | AURA Atelier',
            'user' => $user,
            'products' => $products,
            'orders' => $orders,
            'totalProducts' => count($products),
            'totalUnits' => $totalUnits,
            'totalValuation' => $totalValuation,
        ];

        echo Router::renderView('seller/dashboard', $data, 'admin');
    }

    public function create(): void
    {
        $categories = $this->categoryRepo->findAll();
        $data = [
            'title' => 'Publicar Nueva Prenda | AURA Atelier',
            'categories' => $categories,
            'user' => SessionManager::getUser(),
        ];

        echo Router::renderView('seller/product_form', $data, 'admin');
    }

    public function store(): void
    {
        $user = SessionManager::getUser();

        $name = trim($_POST['name'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 1);
        $description = trim($_POST['description'] ?? '');
        $basePrice = (float)($_POST['base_price'] ?? 0);

        // Procesar variantes
        $sizes = $_POST['variants_size'] ?? [];
        $colors = $_POST['variants_color'] ?? [];
        $colorCodes = $_POST['variants_color_code'] ?? [];
        $stocks = $_POST['variants_stock'] ?? [];
        $addPrices = $_POST['variants_price'] ?? [];

        $variantsData = [];
        for ($i = 0; $i < count($sizes); $i++) {
            if (!empty($sizes[$i]) && !empty($colors[$i])) {
                $variantsData[] = [
                    'size' => $sizes[$i],
                    'color' => $colors[$i],
                    'color_code' => $colorCodes[$i] ?? '#000000',
                    'stock' => (int)($stocks[$i] ?? 0),
                    'additional_price' => (float)($addPrices[$i] ?? 0.0),
                ];
            }
        }

        // Si no se especificaron variantes, crear variantes por defecto
        if (empty($variantsData)) {
            $variantsData = [
                ['size' => 'S', 'color' => 'Estándar', 'color_code' => '#111111', 'stock' => 10, 'additional_price' => 0.0],
                ['size' => 'M', 'color' => 'Estándar', 'color_code' => '#111111', 'stock' => 15, 'additional_price' => 0.0],
                ['size' => 'L', 'color' => 'Estándar', 'color_code' => '#111111', 'stock' => 8, 'additional_price' => 0.0],
            ];
        }

        // Procesar imágenes (URLs o subida de archivo)
        $imagesData = [];
        $imageUrl = trim($_POST['image_url'] ?? '');
        if (!empty($imageUrl)) {
            $imagesData[] = $imageUrl;
        }

        if (!empty($_FILES['image_file']['name'])) {
            try {
                $uploadedPath = $this->storage->upload($_FILES['image_file'], 'products');
                $imagesData[] = $uploadedPath;
            } catch (\Throwable $e) {
                // Silenciosamente usar imagen URL o fallback
            }
        }

        if (empty($imagesData)) {
            $imagesData[] = 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=800&q=80';
        }

        try {
            $this->manageProductUseCase->createProduct(
                $user['id'],
                $categoryId,
                $name,
                $description,
                $basePrice,
                $variantsData,
                $imagesData
            );
            SessionManager::setFlash('success', 'Prenda publicada exitosamente en el catálogo.');
            header('Location: /seller');
            exit;
        } catch (\Throwable $e) {
            SessionManager::setFlash('error', $e->getMessage());
            header('Location: /seller/product/new');
            exit;
        }
    }

    public function edit(array $params): void
    {
        $id = $params['id'] ?? '';
        $user = SessionManager::getUser();
        $product = $this->productRepo->findById($id);

        if (!$product || ($product->sellerId !== $user['id'] && !SessionManager::hasRole('ADMIN'))) {
            SessionManager::setFlash('error', 'Producto no encontrado o sin permisos.');
            header('Location: /seller');
            exit;
        }

        $categories = $this->categoryRepo->findAll();

        $data = [
            'title' => "Editar {$product->name} | AURA Atelier",
            'product' => $product,
            'categories' => $categories,
            'user' => $user,
        ];

        echo Router::renderView('seller/product_edit', $data, 'admin');
    }

    public function update(array $params): void
    {
        $id = $params['id'] ?? '';
        $user = SessionManager::getUser();

        $name = trim($_POST['name'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 1);
        $description = trim($_POST['description'] ?? '');
        $basePrice = (float)($_POST['base_price'] ?? 0);
        $status = $_POST['status'] ?? 'ACTIVE';

        try {
            $this->manageProductUseCase->updateProduct(
                $id,
                $user['id'],
                $categoryId,
                $name,
                $description,
                $basePrice,
                $status
            );

            // Actualizar stock de variantes si fueron enviadas
            if (!empty($_POST['variant_stock'])) {
                foreach ($_POST['variant_stock'] as $vId => $stockVal) {
                    $variant = $this->productRepo->findVariantById($vId);
                    if ($variant && $variant->productId === $id) {
                        $variant->stock = max(0, (int)$stockVal);
                        $this->productRepo->updateVariant($variant);
                    }
                }
            }

            SessionManager::setFlash('success', 'Prenda actualizada correctamente.');
            header('Location: /seller');
            exit;
        } catch (\Throwable $e) {
            SessionManager::setFlash('error', $e->getMessage());
            header('Location: /seller/product/edit/' . $id);
            exit;
        }
    }

    public function delete(array $params): void
    {
        $id = $params['id'] ?? '';
        $user = SessionManager::getUser();

        $this->manageProductUseCase->deleteProduct($id, $user['id']);
        SessionManager::setFlash('success', 'Prenda eliminada del inventario.');
        header('Location: /seller');
        exit;
    }

    public function sales(): void
    {
        $user = SessionManager::getUser();
        $orders = $this->orderRepo->findBySellerId($user['id']);

        $data = [
            'title' => 'Historial de Ventas | AURA Atelier',
            'user' => $user,
            'orders' => $orders,
        ];

        echo Router::renderView('seller/sales', $data, 'admin');
    }
}
