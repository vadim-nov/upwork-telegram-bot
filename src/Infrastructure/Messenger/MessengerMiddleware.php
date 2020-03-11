<?php

namespace App\Infrastructure\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class MessengerMiddleware implements MiddlewareInterface
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
        try {
            $returnedEnvelope = $stack->next()->handle($envelope, $stack);
        } catch (HandlerFailedException $e) {
            throw $e->getNestedExceptions()[0];
        }

        return $returnedEnvelope;
    }
}
