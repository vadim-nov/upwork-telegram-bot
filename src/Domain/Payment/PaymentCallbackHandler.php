<?php

declare(strict_types=1);

namespace App\Domain\Payment;

use App\Domain\Core\Entity\Order;
use App\Domain\TelegramBot\TelegramApiInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class PaymentCallbackHandler implements MessageHandlerInterface
{
    private $telegramApi;
    private $entityManager;

    public function __construct(
        TelegramApiInterface $telegramApi,
        EntityManagerInterface $entityManager
    ) {
        $this->telegramApi = $telegramApi;
        $this->entityManager = $entityManager;
    }

    /**
     * @TODO: DomainException will works only in sync messenger mode!
     * @param PaymentCallback $paymentCallback
     */
    public function __invoke(PaymentCallback $paymentCallback): void
    {
        if (!$paymentCallback->isPaymentSuccess()) {
            throw new \DomainException('Invalid payment callback');
        }

        $order = $this->entityManager->getRepository(Order::class)->findById($paymentCallback->getOrderId());
        if (!$order) {
            throw new \DomainException('Invalid order');
        }

        $order->pay($paymentCallback->getAmountPaid());
        $this->entityManager->flush();

        $user = $order->getUser();
        if ($user->getTelegramRef()) {
            $text = sprintf("Plan upgrade completed👌\nYour current plan: %s", $user->getCurrentPlan()->getName());
            $this->telegramApi->sendMessage($user->getTelegramRef(), $text);
        }
    }
}
