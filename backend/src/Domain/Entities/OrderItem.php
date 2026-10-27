<?php

namespace App\Backend\Domain\Entities;

class OrderItem
{
    public function __construct(
        public readonly ?string $id,
        public string $orderId,
        public ?string $productVariantId,
        public string $productName,
        public string $size,
        public string $color,
        public float $unitPrice,
        public int $quantity,
        public float $subtotal
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            orderId: $data['order_id'],
            productVariantId: $data['product_variant_id'] ?? null,
            productName: $data['product_name'],
            size: $data['size'],
            color: $data['color'],
            unitPrice: (float)$data['unit_price'],
            quantity: (int)$data['quantity'],
            subtotal: (float)$data['subtotal']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->orderId,
            'product_variant_id' => $this->productVariantId,
            'product_name' => $this->productName,
            'size' => $this->size,
            'color' => $this->color,
            'unit_price' => $this->unitPrice,
            'quantity' => $this->quantity,
            'subtotal' => $this->subtotal,
        ];
    }
}
