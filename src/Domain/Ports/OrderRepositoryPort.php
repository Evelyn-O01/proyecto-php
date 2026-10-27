<?php

namespace App\Domain\Ports;

use App\Domain\Entities\Order;
use App\Domain\Entities\OrderItem;
use App\Domain\Entities\Payment;

interface OrderRepositoryPort
{
    public function save(Order $order): Order;

    public function findById(string $id): ?Order;

    public function findByOrderNumber(string $orderNumber): ?Order;

    /**
     * @return Order[]
     */
    public function findByCustomerId(string $customerId): array;

    /**
     * @return Order[]
     */
    public function findBySellerId(string $sellerId): array;

    /**
     * @return Order[]
     */
    public function findAll(int $limit = 50, int $offset = 0): array;

    public function savePayment(Payment $payment): Payment;

    public function updateOrderStatus(string $orderId, string $status): bool;
}
