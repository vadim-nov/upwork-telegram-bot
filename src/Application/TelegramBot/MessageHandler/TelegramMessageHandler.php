<?php

namespace App\Application\TelegramBot\MessageHandler;

use App\Domain\Core\Entity\User;
use App\Domain\Payment\PaymentGatewayInterface;
use App\Domain\TelegramBot\TelegramApiInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Registry;
use App\Domain\Upwork\UpworkRequesterInterface;


abstract class TelegramMessageHandler implements MessageHandlerInterface
{
    protected $telegramApi;
    protected $workflows;
    protected $messageBus;
    protected $upworkRequester;
    protected $entityManager;
    protected $paymentGateway;

    public function __construct(
        TelegramApiInterface $telegramApi,
        Registry $workflows,
        MessageBusInterface $messageBus,
        EntityManagerInterface $entityManager,
        UpworkRequesterInterface $upworkRequester,
        PaymentGatewayInterface $paymentGateway
    )
    {
        $this->messageBus = $messageBus;
        $this->telegramApi = $telegramApi;
        $this->workflows = $workflows;
        $this->upworkRequester = $upworkRequester;
        $this->entityManager = $entityManager;
        $this->paymentGateway = $paymentGateway;
    }
}
