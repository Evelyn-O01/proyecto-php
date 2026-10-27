<?php

namespace App\Frontend\Controllers;

use App\Frontend\Services\BackendClient;
use App\Frontend\Services\CartSession;
use App\Frontend\Services\Session;

class CheckoutController
{
    private BackendClient $client;

    public function __construct()
    {
        $this->client = new BackendClient();
    }

    public function index(): void
    {
        if (CartSession::getItemCount() === 0) {
            Session::flash('info', 'Tu bolsa de compras está vacía.');
            header('Location: /catalog');
            exit;
        }

        $user = Session::getUser();
        $data = [
            'title' => 'Finalizar Compra Segura | AURA Atelier',
            'items' => CartSession::getItems(),
            'subtotal' => CartSession::getSubtotal(),
            'tax' => CartSession::getTax(),
            'shipping' => CartSession::getShipping(),
            'total' => CartSession::getTotal(),
            'user' => $user
        ];

        echo StoreController::render('store/checkout', $data);
    }

    public function process(): void
    {
        if (CartSession::getItemCount() === 0) {
            header('Location: /catalog');
            exit;
        }

        $user = Session::getUser();
        $customerData = [
            'id' => $user['id'] ?? '30000000-0000-0000-0000-000000000001',
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
            'card_number' => $_POST['card_number'] ?? '',
            'exp_month' => $_POST['exp_month'] ?? '',
            'exp_year' => $_POST['exp_year'] ?? '',
            'cvv' => $_POST['cvv'] ?? '',
            'card_holder' => $_POST['card_holder'] ?? '',
        ];

        try {
            $order = $this->client->post('/api/v1/checkout/process', [
                'cart_items' => array_values(CartSession::getItems()),
                'customer' => $customerData,
                'shipping_address' => $shippingAddress,
                'payment' => $paymentPayload
            ]);

            CartSession::clear();
            Session::flash('success', '¡Transacción aprobada con éxito!');
            header('Location: /order/success/' . $order['order_number']);
            exit;
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            header('Location: /checkout');
            exit;
        }
    }

    public function success(array $params): void
    {
        $number = $params['number'] ?? '';
        try {
            $order = $this->client->get('/api/v1/orders/' . $number);
            $data = [
                'title' => "Orden Confirmada #{$order['order_number']} | AURA Atelier",
                'order' => $order
            ];
            echo StoreController::render('store/order_success', $data);
        } catch (\Throwable $e) {
            header('Location: /catalog');
            exit;
        }
    }

    public function myOrders(): void
    {
        $user = Session::getUser();
        if (!$user) {
            Session::flash('info', 'Inicia sesión para consultar tus pedidos.');
            header('Location: /login');
            exit;
        }

        try {
            $orders = $this->client->get('/api/v1/customer/' . $user['id'] . '/orders');
        } catch (\Throwable $e) {
            $orders = [];
        }

        $data = [
            'title' => 'Mis Pedidos & Compras | AURA Atelier',
            'user' => $user,
            'orders' => $orders
        ];

        echo StoreController::render('store/orders', $data);
    }
}
