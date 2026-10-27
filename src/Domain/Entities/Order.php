<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\OrderStatus;

class Order
{
    /**
     * @param OrderItem[] $items
     */
    public function __construct(
        public readonly ?string $id,
        public string $customerId,
        public string $orderNumber,
        public OrderStatus $status,
        public float $totalAmount,
        public float $taxAmount,
        public float $shippingCost,
        public array $shippingAddress,
        public ?string $notes = null,
        public array $items = [],
        public ?Payment $payment = null,
        public ?string $customerName = null,
        public ?string $customerEmail = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null
    ) {
    }

    public static function fromArray(array $data, array $items = [], ?Payment $payment = null): self
    {
        $address = $data['shipping_address'] ?? [];
        if (is_string($address)) {
            $address = json_decode($address, true) ?? [];
        }

        return new self(
            id: $data['id'] ?? null,
            customerId: $data['customer_id'],
            orderNumber: $data['order_number'],
            status: OrderStatus::from($data['status']),
            totalAmount: (float)$data['total_amount'],
            taxAmount: (float)($data['tax_amount'] ?? 0.0),
            shippingCost: (float)($data['shipping_cost'] ?? 0.0),
            shippingAddress: $address,
            notes: $data['notes'] ?? null,
            items: $items,
            payment: $payment,
            customerName: $data['customer_name'] ?? null,
            customerEmail: $data['customer_email'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customerId,
            'order_number' => $this->orderNumber,
            'status' => $this->status->value,
            'total_amount' => $this->totalAmount,
            'tax_amount' => $this->taxAmount,
            'shipping_cost' => $this->shippingCost,
            'shipping_address' => json_encode($this->shippingAddress),
            'notes' => $this->notes,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
