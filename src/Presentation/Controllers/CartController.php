<?php

namespace App\Presentation\Controllers;

use App\Application\Services\CartService;
use App\Infrastructure\Database\PostgresConnection;
use App\Infrastructure\Repositories\PostgresProductRepository;
use App\Presentation\Middlewares\SessionManager;
use App\Presentation\Router;

class CartController
{
    private CartService $cartService;

    public function __construct()
    {
        $pdo = PostgresConnection::getConnection();
        $productRepo = new PostgresProductRepository($pdo);
        $this->cartService = new CartService($productRepo);
    }

    public function index(): void
    {
        $data = [
            'title' => 'Bolsa de Compras | AURA Atelier',
            'items' => $this->cartService->getItems(),
            'itemCount' => $this->cartService->getItemCount(),
            'subtotal' => $this->cartService->getSubtotal(),
            'tax' => $this->cartService->getTax(),
            'shipping' => $this->cartService->getShipping(),
            'total' => $this->cartService->getTotal()
        ];
        echo Router::renderView('store/cart', $data, 'main');
    }

    public function add(): void
    {
        $productId = $_POST['product_id'] ?? '';
        $variantId = $_POST['variant_id'] ?? '';
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        try {
            $this->cartService->addItem($productId, $variantId, $quantity);
            SessionManager::setFlash('success', 'Prenda añadida a tu bolsa de compras.');
        } catch (\Throwable $e) {
            SessionManager::setFlash('error', $e->getMessage());
        }

        $redirect = $_POST['redirect_to'] ?? '/cart';
        header("Location: {$redirect}");
        exit;
    }

    public function update(): void
    {
        $variantId = $_POST['variant_id'] ?? '';
        $quantity = (int)($_POST['quantity'] ?? 1);

        try {
            $this->cartService->updateQuantity($variantId, $quantity);
            SessionManager::setFlash('success', 'Cantidad actualizada.');
        } catch (\Throwable $e) {
            SessionManager::setFlash('error', $e->getMessage());
        }

        header('Location: /cart');
        exit;
    }

    public function remove(array $params): void
    {
        $variantId = $params['id'] ?? '';
        $this->cartService->removeItem($variantId);
        SessionManager::setFlash('success', 'Prenda retirada de la bolsa.');
        header('Location: /cart');
        exit;
    }
}
