<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Integration;

use App\Domain\Payment\PaymentCallback;
use App\Domain\Payment\PaymentGatewayInterface;
use App\Infrastructure\Integration\Payeer;
use Money\Money;

class PayeerMock implements PaymentGatewayInterface
{
    private $payeer;

    public function __construct(Payeer $payeer)
    {
        $this->payeer = $payeer;
    }

    public function generatePaymentPageUrl(string $orderId, Money $money): string
    {
        return 'https://payer.mock/'.$orderId;
    }

    public function parsePaymentCallback(array $body): PaymentCallback
    {
        return $this->payeer->parsePaymentCallback($body);
    }
}