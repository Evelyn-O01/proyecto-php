<?php

namespace App\Application\UseCases\Checkout;

use App\Domain\Entities\Order;
use App\Domain\Entities\OrderItem;
use App\Domain\Entities\Payment;
use App\Domain\Ports\OrderRepositoryPort;
use App\Domain\Ports\PaymentGatewayPort;
use App\Domain\Ports\PaymentRequest;
use App\Domain\Ports\ProductRepositoryPort;
use App\Domain\ValueObjects\OrderStatus;
use RuntimeException;

class ProcessCheckoutUseCase
{
    public function __construct(
        private OrderRepositoryPort $orderRepo,
        private ProductRepositoryPort $productRepo,
        private PaymentGatewayPort $paymentGateway
    ) {
    }

    /**
     * @param array $cartItems
     * @param array $customerData ['id', 'name', 'email', 'phone']
     * @param array $shippingAddress ['street', 'city', 'state', 'zip', 'recipient', 'phone']
     * @param array $paymentPayload ['card_number', 'exp_month', 'exp_year', 'cvv', 'stripe_token', etc.]
     * @return Order
     */
    public function execute(
        array $cartItems,
        array $customerData,
        array $shippingAddress,
        array $paymentPayload
    ): Order {
        if (empty($cartItems)) {
            throw new RuntimeException("El carrito de compras está vacío.");
        }

        // 1. Validar stock de todos los artículos antes de proceder
        $subtotal = 0.0;
        $orderItems = [];

        foreach ($cartItems as $item) {
            $variant = $this->productRepo->findVariantById($item['variant_id']);
            if (!$variant || $variant->stock < $item['quantity']) {
                $stockAvail = $variant ? $variant->stock : 0;
                throw new RuntimeException("El producto '{$item['name']}' ({$item['size']}) no tiene stock suficiente. Disponible: {$stockAvail}");
            }

            $itemSubtotal = $item['unit_price'] * $item['quantity'];
            $subtotal += $itemSubtotal;

            $orderItems[] = new OrderItem(
                id: null,
                orderId: '',
                productVariantId: $variant->id,
                productName: $item['name'],
                size: $item['size'],
                color: $item['color'],
                unitPrice: (float)$item['unit_price'],
                quantity: (int)$item['quantity'],
                subtotal: $itemSubtotal
            );
        }

        $tax = round($subtotal * 0.16, 2);
        $shipping = $subtotal >= 999.00 ? 0.00 : 99.00;
        $total = round($subtotal + $shipping, 2);

        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        // 2. Procesar pago a través de la pasarela (puerto hexagonal)
        $paymentRequest = new PaymentRequest(
            amount: $total,
            currency: 'MXN',
            orderNumber: $orderNumber,
            customerEmail: $customerData['email'],
            customerName: $customerData['name'],
            paymentData: $paymentPayload
        );

        $paymentResult = $this->paymentGateway->charge($paymentRequest);

        if (!$paymentResult->success) {
            throw new RuntimeException("Pago rechazado: " . ($paymentResult->errorMessage ?: "Error desconocido"));
        }

        // 3. Crear Pedido y Guardar en PostgreSQL
        $order = new Order(
            id: null,
            customerId: $customerData['id'],
            orderNumber: $orderNumber,
            status: OrderStatus::PAID,
            totalAmount: $total,
            taxAmount: $tax,
            shippingCost: $shipping,
            shippingAddress: $shippingAddress,
            notes: "Pago procesado vía " . $paymentResult->gateway,
            items: $orderItems,
            customerName: $customerData['name'],
            customerEmail: $customerData['email']
        );

        $savedOrder = $this->orderRepo->save($order);

        // 4. Guardar registro del Pago
        $paymentEntity = new Payment(
            id: null,
            orderId: $savedOrder->id,
            gateway: $paymentResult->gateway,
            transactionReference: $paymentResult->transactionReference,
            amount: $total,
            status: 'APPROVED',
            cardLast4: $paymentResult->cardLast4,
            paymentMethod: $paymentResult->paymentMethod,
            metadata: $paymentResult->rawPayload
        );
        $this->orderRepo->savePayment($paymentEntity);

        // 5. Decrementar stock atómicamente
        foreach ($cartItems as $item) {
            $this->productRepo->decrementStock($item['variant_id'], $item['quantity']);
        }

        return $savedOrder;
    }
}
