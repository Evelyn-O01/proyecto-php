<?php

namespace App\Backend\Controllers;

use App\Backend\Domain\Entities\Order;
use App\Backend\Domain\Entities\OrderItem;
use App\Backend\Domain\Entities\Payment;
use App\Backend\Domain\Ports\OrderRepositoryPort;
use App\Backend\Domain\Ports\PaymentGatewayPort;
use App\Backend\Domain\Ports\PaymentRequest;
use App\Backend\Domain\Ports\ProductRepositoryPort;
use App\Backend\Domain\ValueObjects\OrderStatus;
use App\Backend\Infrastructure\Database\PostgresConnection;
use App\Backend\Infrastructure\Payment\PaymentGatewayFactory;
use App\Backend\Infrastructure\Repositories\PostgresOrderRepository;
use App\Backend\Infrastructure\Repositories\PostgresProductRepository;
use RuntimeException;

class OrderController
{
    private OrderRepositoryPort $orderRepo;
    private ProductRepositoryPort $productRepo;
    private PaymentGatewayPort $paymentGateway;

    public function __construct(
        ?OrderRepositoryPort $orderRepo = null,
        ?ProductRepositoryPort $productRepo = null,
        ?PaymentGatewayPort $gateway = null
    ) {
        $pdo = PostgresConnection::getConnection();
        $this->orderRepo = $orderRepo ?: new PostgresOrderRepository($pdo);
        $this->productRepo = $productRepo ?: new PostgresProductRepository($pdo);
        $this->paymentGateway = $gateway ?: PaymentGatewayFactory::create();
    }

    public function processCheckout(
        array $cartItems,
        array $customerData,
        array $shippingAddress,
        array $paymentPayload
    ): array {
        if (empty($cartItems)) {
            throw new RuntimeException("La bolsa de compras está vacía.");
        }

        // 1. Validar existencias
        $subtotal = 0.0;
        $orderItems = [];

        foreach ($cartItems as $item) {
            $variant = $this->productRepo->findVariantById($item['variant_id']);
            if (!$variant || $variant->stock < $item['quantity']) {
                $stockAvail = $variant ? $variant->stock : 0;
                throw new RuntimeException("Existencias insuficientes para '{$item['name']}'. Disponible: {$stockAvail}");
            }

            $lineSubtotal = $item['unit_price'] * $item['quantity'];
            $subtotal += $lineSubtotal;

            $orderItems[] = new OrderItem(
                id: null,
                orderId: '',
                productVariantId: $variant->id,
                productName: $item['name'],
                size: $item['size'],
                color: $item['color'],
                unitPrice: (float)$item['unit_price'],
                quantity: (int)$item['quantity'],
                subtotal: $lineSubtotal
            );
        }

        $tax = round($subtotal * 0.16, 2);
        $shipping = $subtotal >= 999.00 ? 0.00 : 99.00;
        $total = round($subtotal + $shipping, 2);

        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        // 2. Cobro en Pasarela de Pago
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
            throw new RuntimeException("Cobro rechazado: " . ($paymentResult->errorMessage ?: "Error bancario"));
        }

        // 3. Crear Pedido
        $order = new Order(
            id: null,
            customerId: $customerData['id'],
            orderNumber: $orderNumber,
            status: OrderStatus::PAID,
            totalAmount: $total,
            taxAmount: $tax,
            shippingCost: $shipping,
            shippingAddress: $shippingAddress,
            notes: "Pago liquidado vía " . $paymentResult->gateway,
            items: $orderItems,
            customerName: $customerData['name'],
            customerEmail: $customerData['email']
        );

        $savedOrder = $this->orderRepo->save($order);

        // 4. Registrar Pago
        $payment = new Payment(
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
        $this->orderRepo->savePayment($payment);

        // 5. Decrementar stock
        foreach ($cartItems as $item) {
            $this->productRepo->decrementStock($item['variant_id'], $item['quantity']);
        }

        return $this->formatOrderData($savedOrder, $payment);
    }

    public function getByNumber(string $orderNumber): ?array
    {
        $order = $this->orderRepo->findByOrderNumber($orderNumber);
        return $order ? $this->formatOrderData($order, $order->payment) : null;
    }

    public function getByCustomer(string $customerId): array
    {
        $orders = $this->orderRepo->findByCustomerId($customerId);
        return array_map(fn(Order $o) => $this->formatOrderData($o, $o->payment), $orders);
    }

    public function getBySeller(string $sellerId): array
    {
        $orders = $this->orderRepo->findBySellerId($sellerId);
        return array_map(fn(Order $o) => $this->formatOrderData($o, $o->payment), $orders);
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $orders = $this->orderRepo->findAll($limit, $offset);
        return array_map(fn(Order $o) => $this->formatOrderData($o, $o->payment), $orders);
    }

    private function formatOrderData(Order $order, ?Payment $payment = null): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->orderNumber,
            'status' => $order->status->value,
            'status_label' => $order->status->label(),
            'status_badge' => $order->status->badgeClass(),
            'total_amount' => $order->totalAmount,
            'tax_amount' => $order->taxAmount,
            'shipping_cost' => $order->shippingCost,
            'shipping_address' => $order->shippingAddress,
            'customer_name' => $order->customerName,
            'customer_email' => $order->customerEmail,
            'created_at' => $order->createdAt,
            'items' => array_map(fn(OrderItem $i) => $i->toArray(), $order->items),
            'payment' => $payment ? $payment->toArray() : null,
        ];
    }
}
