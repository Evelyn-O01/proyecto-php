<?php

namespace App\Backend\Infrastructure\Payment;

use App\Backend\Domain\Ports\PaymentGatewayPort;

class PaymentGatewayFactory
{
    public static function create(?string $gateway = null): PaymentGatewayPort
    {
        $appConfig = require __DIR__ . '/../../../config/app.php';
        $gatewayName = strtolower($gateway ?: ($appConfig['payment_gateway'] ?? 'simulated'));

        return match ($gatewayName) {
            'stripe' => new StripePaymentGateway($appConfig['stripe_secret'] ?? ''),
            default => new SimulatedPaymentGateway(),
        };
    }
}
