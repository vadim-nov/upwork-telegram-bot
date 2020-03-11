<?php

namespace App\Application\TelegramBot\Middleware;

use App\Application\TelegramBot\Message\LogChatMessage;
use App\Application\TelegramBot\Message\TelegramMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class TelegramMessageLoggingMiddleware implements MiddlewareInterface
{
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();
        if ($message instanceof TelegramMessage) {
            $this->messageBus->dispatch(
                new LogChatMessage(
                    (int)$message->getUser()->getTelegramRef(),
                    (string)$message->getMessage(),
                    true
                )
            );
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
