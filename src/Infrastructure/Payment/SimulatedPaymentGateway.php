<?php

namespace App\Infrastructure\Payment;

use App\Domain\Ports\PaymentGatewayPort;
use App\Domain\Ports\PaymentRequest;
use App\Domain\Ports\PaymentResponse;

class SimulatedPaymentGateway implements PaymentGatewayPort
{
    public function getName(): string
    {
        return 'SIMULATED';
    }

    public function charge(PaymentRequest $request): PaymentResponse
    {
        $cardNumber = preg_replace('/\D/', '', $request->paymentData['card_number'] ?? '4242424242424242');
        $cardLast4 = substr($cardNumber, -4) ?: '4242';
        
        // Determinar marca
        $brand = 'Visa';
        if (str_starts_with($cardNumber, '5')) {
            $brand = 'Mastercard';
        } elseif (str_starts_with($cardNumber, '3')) {
            $brand = 'American Express';
        }

        // Simular tarjeta de prueba que falle intencionalmente si termina en 0000 o 9999
        if ($cardLast4 === '0000' || $cardLast4 === '9999') {
            return new PaymentResponse(
                success: false,
                transactionReference: 'FAIL-' . strtoupper(bin2hex(random_bytes(6))),
                gateway: 'SIMULATED',
                cardLast4: $cardLast4,
                paymentMethod: "Tarjeta {$brand}",
                errorMessage: 'Fondos insuficientes o transacción rechazada por el banco emisor.',
                rawPayload: ['status' => 'declined', 'code' => 'insufficient_funds']
            );
        }

        // Generar referencia bancaria realista
        $reference = 'TXN-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $authCode = 'AUTH' . rand(100000, 999999);

        return new PaymentResponse(
            success: true,
            transactionReference: $reference,
            gateway: 'SIMULATED',
            cardLast4: $cardLast4,
            paymentMethod: "Tarjeta {$brand}",
            errorMessage: null,
            rawPayload: [
                'status' => 'approved',
                'auth_code' => $authCode,
                'network' => $brand,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'order_number' => $request->orderNumber,
                'timestamp' => date('c'),
            ]
        );
    }
}
