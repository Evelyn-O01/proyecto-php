<?php

namespace App\Domain\Ports;

class PaymentRequest
{
    public function __construct(
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $orderNumber,
        public readonly string $customerEmail,
        public readonly string $customerName,
        public readonly array $paymentData = []
    ) {
    }
}

class PaymentResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly string $transactionReference,
        public readonly string $gateway,
        public readonly ?string $cardLast4 = null,
        public readonly ?string $paymentMethod = null,
        public readonly ?string $errorMessage = null,
        public readonly array $rawPayload = []
    ) {
    }
}

interface PaymentGatewayPort
{
    public function getName(): string;
    public function charge(PaymentRequest $request): PaymentResponse;
}
