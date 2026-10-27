<?php

namespace App\Backend\Domain\Ports;

use App\Backend\Domain\Entities\Order;
use App\Backend\Domain\Entities\OrderItem;
use App\Backend\Domain\Entities\Payment;

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
