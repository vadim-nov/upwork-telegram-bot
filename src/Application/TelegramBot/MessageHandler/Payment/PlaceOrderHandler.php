<?php

declare(strict_types=1);

namespace App\Application\TelegramBot\MessageHandler\Payment;

use App\Application\TelegramBot\Message\HelpMessage;
use App\Application\TelegramBot\MessageHandler\TelegramMessageHandler;
use App\Domain\Core\Entity\Plan;
use App\Domain\Core\Entity\Order;
use App\Application\TelegramBot\Message\Payment\PlaceOrder;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class PlaceOrderHandler extends TelegramMessageHandler implements MessageHandlerInterface
{
    public function __invoke(PlaceOrder $message): void
    {
        $user = $message->getUser();
        if ($message->isCancel()) {
            $this->telegramApi->removeMessage($user->getTelegramRef(), $message->getId());

            $this->messageBus->dispatch(new HelpMessage($message->getId(), $message->getMessage(), $message->getUser()));
            return;
        }

        $plan = $this->entityManager->getRepository(Plan::class)->findOneByName($message->getPlanName());
        if (!$plan) {
            throw new \DomainException('Invalid plan');
        }

        $order = new Order(Uuid::uuid4()->toString(), $user, $plan);
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        $url = $this->paymentGateway->generatePaymentPageUrl($order->getId(), $order->getPlan()->getPrice());

        $keyboard = [
            'inline_keyboard' => [
                [['url' => $url, 'text' => 'Pay']]
            ],
            'one_time_keyboard' => true,
        ];

        $this->telegramApi->sendMessage($user->getTelegramRef(), 'Order successfully placed.', $keyboard);
    }
}
