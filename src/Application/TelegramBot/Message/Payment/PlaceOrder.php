<?php

declare(strict_types=1);

namespace App\Application\TelegramBot\Message\Payment;

use App\Domain\Core\Entity\User;
use App\Application\TelegramBot\Message\TelegramMessage;

class PlaceOrder extends TelegramMessage
{
    private $planName;
    private $isCancel;

    public function __construct(int $id, string $message, User $user, string $planName, bool $isCancel)
    {
        parent::__construct($id, $message, $user);
        $this->planName = $planName;
        $this->isCancel = $isCancel;
    }

    public function getPlanName(): string
    {
        return $this->planName;
    }

    public function isCancel(): bool
    {
        return $this->isCancel;
    }
}
