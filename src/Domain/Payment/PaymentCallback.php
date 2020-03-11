<?php

declare(strict_types=1);

namespace App\Domain\Payment;

use Money\Money;

class PaymentCallback
{
    private $orderId;
    private $isPaymentSuccess;
    private $amountPaid;

    public function __construct(
        string $orderId,
        bool $isPaymentSuccess,
        ?Money $amountPaid = null
    ) {
        $this->orderId = $orderId;
        $this->isPaymentSuccess = $isPaymentSuccess;
        $this->amountPaid = $amountPaid;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function isPaymentSuccess(): bool
    {
        return $this->isPaymentSuccess;
    }

    public function getAmountPaid(): ?Money
    {
        return $this->amountPaid;
    }
}