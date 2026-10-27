<?php

namespace App\Backend\Endpoints;

use App\Backend\Controllers\OrderController;

class CheckoutEndpoints
{
    private OrderController $controller;

    public function __construct()
    {
        $this->controller = new OrderController();
    }

    public function process(): void
    {
        $body = ApiRouter::getJsonBody();
        $cartItems = $body['cart_items'] ?? [];
        $customerData = $body['customer'] ?? [];
        $shippingAddress = $body['shipping_address'] ?? [];
        $paymentPayload = $body['payment'] ?? [];

        try {
            $order = $this->controller->processCheckout(
                $cartItems,
                $customerData,
                $shippingAddress,
                $paymentPayload
            );
            ApiRouter::json(['success' => true, 'data' => $order], 201);
        } catch (\Throwable $e) {
            ApiRouter::json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function getOrder(array $params): void
    {
        $number = $params['number'] ?? '';
        $order = $this->controller->getByNumber($number);
        if ($order) {
            ApiRouter::json(['success' => true, 'data' => $order]);
        } else {
            ApiRouter::json(['success' => false, 'error' => 'Pedido no encontrado'], 404);
        }
    }

    public function getCustomerOrders(array $params): void
    {
        $customerId = $params['id'] ?? '';
        $orders = $this->controller->getByCustomer($customerId);
        ApiRouter::json(['success' => true, 'data' => $orders]);
    }
}
