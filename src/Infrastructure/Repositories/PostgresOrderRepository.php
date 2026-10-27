<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Order;
use App\Domain\Entities\OrderItem;
use App\Domain\Entities\Payment;
use App\Domain\Ports\OrderRepositoryPort;
use PDO;

class PostgresOrderRepository implements OrderRepositoryPort
{
    public function __construct(private PDO $db)
    {
    }

    public function save(Order $order): Order
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO orders (id, customer_id, order_number, status, total_amount, tax_amount, shipping_cost, shipping_address, notes)
                VALUES (COALESCE(:id, uuid_generate_v4()), :customer_id, :order_number, :status, :total_amount, :tax_amount, :shipping_cost, :shipping_address, :notes)
                RETURNING id, created_at, updated_at
            ");
            $stmt->execute([
                'id' => $order->id,
                'customer_id' => $order->customerId,
                'order_number' => $order->orderNumber,
                'status' => $order->status->value,
                'total_amount' => $order->totalAmount,
                'tax_amount' => $order->taxAmount,
                'shipping_cost' => $order->shippingCost,
                'shipping_address' => json_encode($order->shippingAddress),
                'notes' => $order->notes,
            ]);
            $row = $stmt->fetch();
            $orderId = $row['id'];

            // Insert items
            $itemStmt = $this->db->prepare("
                INSERT INTO order_items (id, order_id, product_variant_id, product_name, size, color, unit_price, quantity, subtotal)
                VALUES (COALESCE(:id, uuid_generate_v4()), :order_id, :product_variant_id, :product_name, :size, :color, :unit_price, :quantity, :subtotal)
            ");

            foreach ($order->items as $item) {
                $itemStmt->execute([
                    'id' => $item->id,
                    'order_id' => $orderId,
                    'product_variant_id' => $item->productVariantId,
                    'product_name' => $item->productName,
                    'size' => $item->size,
                    'color' => $item->color,
                    'unit_price' => $item->unitPrice,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                ]);
            }

            $this->db->commit();
            return $this->findById($orderId);
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function findById(string $id): ?Order
    {
        $stmt = $this->db->prepare("
            SELECT o.*, u.name as customer_name, u.email as customer_email
            FROM orders o
            JOIN users u ON o.customer_id = u.id
            WHERE o.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return $this->hydrateOrderWithRelations($row);
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        $stmt = $this->db->prepare("
            SELECT o.*, u.name as customer_name, u.email as customer_email
            FROM orders o
            JOIN users u ON o.customer_id = u.id
            WHERE o.order_number = :order_number
        ");
        $stmt->execute(['order_number' => $orderNumber]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return $this->hydrateOrderWithRelations($row);
    }

    public function findByCustomerId(string $customerId): array
    {
        $stmt = $this->db->prepare("
            SELECT o.*, u.name as customer_name, u.email as customer_email
            FROM orders o
            JOIN users u ON o.customer_id = u.id
            WHERE o.customer_id = :customer_id
            ORDER BY o.created_at DESC
        ");
        $stmt->execute(['customer_id' => $customerId]);
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => $this->hydrateOrderWithRelations($row), $rows);
    }

    public function findBySellerId(string $sellerId): array
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT o.*, u.name as customer_name, u.email as customer_email
            FROM orders o
            JOIN users u ON o.customer_id = u.id
            JOIN order_items oi ON oi.order_id = o.id
            JOIN product_variants pv ON oi.product_variant_id = pv.id
            JOIN products p ON pv.product_id = p.id
            WHERE p.seller_id = :seller_id
            ORDER BY o.created_at DESC
        ");
        $stmt->execute(['seller_id' => $sellerId]);
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => $this->hydrateOrderWithRelations($row), $rows);
    }

    public function findAll(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT o.*, u.name as customer_name, u.email as customer_email
            FROM orders o
            JOIN users u ON o.customer_id = u.id
            ORDER BY o.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => $this->hydrateOrderWithRelations($row), $rows);
    }

    public function savePayment(Payment $payment): Payment
    {
        $stmt = $this->db->prepare("
            INSERT INTO payments (id, order_id, gateway, transaction_reference, amount, status, card_last4, payment_method, metadata)
            VALUES (COALESCE(:id, uuid_generate_v4()), :order_id, :gateway, :transaction_reference, :amount, :status, :card_last4, :payment_method, :metadata)
            RETURNING id, created_at
        ");
        $stmt->execute([
            'id' => $payment->id,
            'order_id' => $payment->orderId,
            'gateway' => $payment->gateway,
            'transaction_reference' => $payment->transactionReference,
            'amount' => $payment->amount,
            'status' => $payment->status,
            'card_last4' => $payment->cardLast4,
            'payment_method' => $payment->paymentMethod,
            'metadata' => $payment->metadata ? json_encode($payment->metadata) : null,
        ]);
        $row = $stmt->fetch();
        return new Payment(
            id: $row['id'],
            orderId: $payment->orderId,
            gateway: $payment->gateway,
            transactionReference: $payment->transactionReference,
            amount: $payment->amount,
            status: $payment->status,
            cardLast4: $payment->cardLast4,
            paymentMethod: $payment->paymentMethod,
            metadata: $payment->metadata,
            createdAt: $row['created_at']
        );
    }

    public function updateOrderStatus(string $orderId, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE orders SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute([
            'id' => $orderId,
            'status' => $status
        ]);
    }

    private function hydrateOrderWithRelations(array $row): Order
    {
        $itemStmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id = :id");
        $itemStmt->execute(['id' => $row['id']]);
        $itemRows = $itemStmt->fetchAll();
        $items = array_map(fn($i) => OrderItem::fromArray($i), $itemRows);

        $payStmt = $this->db->prepare("SELECT * FROM payments WHERE order_id = :id ORDER BY created_at DESC LIMIT 1");
        $payStmt->execute(['id' => $row['id']]);
        $payRow = $payStmt->fetch();
        $payment = $payRow ? Payment::fromArray($payRow) : null;

        return Order::fromArray($row, $items, $payment);
    }
}
