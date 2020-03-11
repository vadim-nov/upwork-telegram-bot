<?php

declare(strict_types=1);

namespace App\Domain\Core\Event;

use App\Domain\Core\Entity\Order;
use Knp\Rad\DomainEvent\Event;

class OrderEvent extends Event
{
    public const TYPE_PAID = 'paid';
    public const TYPE_PLACED = 'placed';

    private $order;
    private $type;

    public function __construct(Order $order, string $type)
    {
        parent::__construct(self::class);
        $this->order = $order;
        $this->type = $type;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getType(): string
    {
        return $this->type;
    }
}