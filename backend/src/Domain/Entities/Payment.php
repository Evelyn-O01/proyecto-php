<?php

namespace App\Backend\Domain\Entities;

class Payment
{
    public function __construct(
        public readonly ?string $id,
        public string $orderId,
        public string $gateway,
        public string $transactionReference,
        public float $amount,
        public string $status, // PENDING, APPROVED, REJECTED
        public ?string $cardLast4 = null,
        public ?string $paymentMethod = null,
        public ?array $metadata = null,
        public ?string $createdAt = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        $meta = $data['metadata'] ?? null;
        if (is_string($meta)) {
            $meta = json_decode($meta, true);
        }

        return new self(
            id: $data['id'] ?? null,
            orderId: $data['order_id'],
            gateway: $data['gateway'],
            transactionReference: $data['transaction_reference'],
            amount: (float)$data['amount'],
            status: $data['status'],
            cardLast4: $data['card_last4'] ?? null,
            paymentMethod: $data['payment_method'] ?? null,
            metadata: $meta,
            createdAt: $data['created_at'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->orderId,
            'gateway' => $this->gateway,
            'transaction_reference' => $this->transactionReference,
            'amount' => $this->amount,
            'status' => $this->status,
            'card_last4' => $this->cardLast4,
            'payment_method' => $this->paymentMethod,
            'metadata' => $this->metadata ? json_encode($this->metadata) : null,
            'created_at' => $this->createdAt,
        ];
    }
}
