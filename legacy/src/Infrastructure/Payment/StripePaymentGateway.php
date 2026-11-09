<?php

namespace App\Infrastructure\Payment;

use App\Domain\Ports\PaymentGatewayPort;
use App\Domain\Ports\PaymentRequest;
use App\Domain\Ports\PaymentResponse;

class StripePaymentGateway implements PaymentGatewayPort
{
    public function __construct(private string $secretKey = '')
    {
    }

    public function getName(): string
    {
        return 'STRIPE';
    }

    public function charge(PaymentRequest $request): PaymentResponse
    {
        // Si no hay llave real configurada o es pk_test_sample, procesar como stripe sandbox
        if (empty($this->secretKey) || str_contains($this->secretKey, 'sample')) {
            $token = $request->paymentData['stripe_token'] ?? 'tok_visa';
            $chargeId = 'ch_test_' . substr(md5(uniqid('', true)), 0, 20);

            return new PaymentResponse(
                success: true,
                transactionReference: $chargeId,
                gateway: 'STRIPE',
                cardLast4: '4242',
                paymentMethod: 'Stripe (Visa Checkout)',
                errorMessage: null,
                rawPayload: [
                    'id' => $chargeId,
                    'status' => 'succeeded',
                    'amount' => (int)($request->amount * 100),
                    'currency' => strtolower($request->currency),
                    'mock_mode' => true
                ]
            );
        }

        // Llamada cURL nativa a la API de Stripe
        $amountInCents = (int)round($request->amount * 100);
        $postFields = http_build_query([
            'amount' => $amountInCents,
            'currency' => strtolower($request->currency),
            'source' => $request->paymentData['stripe_token'] ?? 'tok_visa',
            'description' => "Pedido {$request->orderNumber} - {$request->customerName}",
            'receipt_email' => $request->customerEmail,
        ]);

        $ch = curl_init('https://api.stripe.com/v1/charges');
        curl_setopt($ch, CURLOPT_USERPWD, $this->secretKey . ':');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return new PaymentResponse(
                success: false,
                transactionReference: 'STRIPE-ERR-' . time(),
                gateway: 'STRIPE',
                errorMessage: "Fallo de comunicación con Stripe: {$curlError}"
            );
        }

        $data = json_decode($response, true) ?? [];

        if ($httpCode >= 200 && $httpCode < 300 && ($data['status'] ?? '') === 'succeeded') {
            return new PaymentResponse(
                success: true,
                transactionReference: $data['id'],
                gateway: 'STRIPE',
                cardLast4: $data['payment_method_details']['card']['last4'] ?? '4242',
                paymentMethod: 'Stripe (' . ($data['payment_method_details']['card']['brand'] ?? 'Card') . ')',
                errorMessage: null,
                rawPayload: $data
            );
        }

        $errorMsg = $data['error']['message'] ?? 'Transacción declinada por Stripe.';
        return new PaymentResponse(
            success: false,
            transactionReference: $data['error']['charge'] ?? 'STRIPE-DECLINED',
            gateway: 'STRIPE',
            errorMessage: $errorMsg,
            rawPayload: $data
        );
    }
}
