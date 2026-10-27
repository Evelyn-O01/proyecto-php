<?php

namespace App\Presentation\Controllers;

use App\Application\Services\CartService;
use App\Application\UseCases\Checkout\ProcessCheckoutUseCase;
use App\Infrastructure\Database\PostgresConnection;
use App\Infrastructure\Payment\PaymentGatewayFactory;
use App\Infrastructure\Repositories\PostgresOrderRepository;
use App\Infrastructure\Repositories\PostgresProductRepository;
use App\Presentation\Middlewares\SessionManager;
use App\Presentation\Router;

class CheckoutController
{
    private CartService $cartService;
    private ProcessCheckoutUseCase $checkoutUseCase;
    private PostgresOrderRepository $orderRepo;

    public function __construct()
    {
        $pdo = PostgresConnection::getConnection();
        $productRepo = new PostgresProductRepository($pdo);
        $this->orderRepo = new PostgresOrderRepository($pdo);
        $this->cartService = new CartService($productRepo);

        $gateway = PaymentGatewayFactory::create();
        $this->checkoutUseCase = new ProcessCheckoutUseCase($this->orderRepo, $productRepo, $gateway);
    }

    public function index(): void
    {
        if ($this->cartService->getItemCount() === 0) {
            SessionManager::setFlash('info', 'Tu bolsa de compras está vacía. Añade prendas para continuar.');
            header('Location: /catalog');
            exit;
        }

        $user = SessionManager::getUser();
        $data = [
            'title' => 'Finalizar Compra Segura | AURA Atelier',
            'items' => $this->cartService->getItems(),
            'subtotal' => $this->cartService->getSubtotal(),
            'tax' => $this->cartService->getTax(),
            'shipping' => $this->cartService->getShipping(),
            'total' => $this->cartService->getTotal(),
            'user' => $user
        ];

        echo Router::renderView('store/checkout', $data, 'main');
    }

    public function process(): void
    {
        if ($this->cartService->getItemCount() === 0) {
            header('Location: /catalog');
            exit;
        }

        $user = SessionManager::getUser();
        
        // Datos del cliente (o usuario logueado o invitado)
        $customerData = [
            'id' => $user['id'] ?? '30000000-0000-0000-0000-000000000001', // Fallback default client
            'name' => trim($_POST['customer_name'] ?? ($user['name'] ?? 'Cliente AURA')),
            'email' => trim($_POST['customer_email'] ?? ($user['email'] ?? 'cliente@tiendaropa.com')),
            'phone' => trim($_POST['customer_phone'] ?? '')
        ];

        $shippingAddress = [
            'recipient' => $customerData['name'],
            'street' => trim($_POST['street'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'state' => trim($_POST['state'] ?? ''),
            'zip' => trim($_POST['zip'] ?? ''),
            'phone' => $customerData['phone']
        ];

        $paymentPayload = [
            'gateway_type' => $_POST['gateway_type'] ?? 'simulated',
            'card_number' => $_POST['card_number'] ?? '',
            'exp_month' => $_POST['exp_month'] ?? '',
            'exp_year' => $_POST['exp_year'] ?? '',
            'cvv' => $_POST['cvv'] ?? '',
            'card_holder' => $_POST['card_holder'] ?? '',
            'stripe_token' => $_POST['stripe_token'] ?? null
        ];

        try {
            $order = $this->checkoutUseCase->execute(
                $this->cartService->getItems(),
                $customerData,
                $shippingAddress,
                $paymentPayload
            );

            // Limpiar carrito tras pago exitoso
            $this->cartService->clear();

            SessionManager::setFlash('success', '¡Tu orden ha sido confirmada y procesada con éxito!');
            header('Location: /order/success/' . $order->orderNumber);
            exit;
        } catch (\Throwable $e) {
            SessionManager::setFlash('error', $e->getMessage());
            header('Location: /checkout');
            exit;
        }
    }

    public function success(array $params): void
    {
        $orderNumber = $params['number'] ?? '';
        $order = $this->orderRepo->findByOrderNumber($orderNumber);

        if (!$order) {
            header('Location: /catalog');
            exit;
        }

        $data = [
            'title' => "Orden Confirmada #{$order->orderNumber} | AURA Atelier",
            'order' => $order
        ];

        echo Router::renderView('store/order_success', $data, 'main');
    }

    public function myOrders(): void
    {
        $user = SessionManager::getUser();
        if (!$user) {
            SessionManager::setFlash('info', 'Inicia sesión para consultar tus pedidos.');
            header('Location: /login');
            exit;
        }

        $orders = $this->orderRepo->findByCustomerId($user['id']);

        $data = [
            'title' => 'Mis Pedidos & Seguimiento | AURA Atelier',
            'user' => $user,
            'orders' => $orders
        ];

        echo Router::renderView('store/orders', $data, 'main');
    }
}
