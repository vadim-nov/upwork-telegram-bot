<?php

declare(strict_types=1);

namespace App\Domain\Payment;

use Money\Money;

interface PaymentGatewayInterface
{
    public function generatePaymentPageUrl(string $orderId, Money $money): string;

    public function parsePaymentCallback(array $body): PaymentCallback;
}