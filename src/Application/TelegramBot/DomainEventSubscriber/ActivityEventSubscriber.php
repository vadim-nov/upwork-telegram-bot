<?php

declare(strict_types=1);

namespace App\Application\TelegramBot\DomainEventSubscriber;

use App\Domain\Upwork\FilterJobUpdatesFromStopWords;
use App\Domain\Core\Entity\User;
use App\Domain\Core\Entity\UserSearch;
use App\Domain\Core\Event\OrderEvent;
use App\Domain\Core\Event\UserEvent;
use App\Domain\TelegramBot\TelegramApiInterface;
use App\Application\Upwork\Message\SaveUpworkDataMessage;
use App\Domain\Upwork\UpworkRequesterInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ActivityEventSubscriber
{
    private $telegramApi;
    private $upworkRequester;
    private $messageBus;
    private $logger;

    public function __construct(
        LoggerInterface $telegramActionLogger,
        TelegramApiInterface $telegramApi,
        UpworkRequesterInterface $upworkRequester,
        MessageBusInterface $messageBus
    ) {
        $this->logger = $telegramActionLogger;
        $this->telegramApi = $telegramApi;
        $this->upworkRequester = $upworkRequester;
        $this->messageBus = $messageBus;
    }

    public function onBotStarted(User $user)
    {
        $message = sprintf('%s started bot', $this->formatName($user));
        $this->sendLog($message);
    }

    public function onUserEvent(UserEvent $event)
    {
        $user = $event->getUser();
        $search = $event->getSearch();

        if ($event->getType() === UserEvent::TYPE_SEARCH_ADDED) {
            $message = sprintf('%s added search <b>%s</b>', $this->formatName($user), $search->getSearchName());
            $this->sendJobUpdates($search);
            $this->sendLog($message);
        }

        if ($event->getType() === UserEvent::TYPE_SEARCH_REMOVED) {
            $message = sprintf('%s removed search <b>%s</b>', $this->formatName($user), $search->getSearchName());
            $this->sendLog($message);
        }
    }

    public function onOrderEvent(OrderEvent $event): void
    {
        $user = $event->getOrder()->getUser();
        $plan = $event->getOrder()->getPlan();

        if ($event->getType() === OrderEvent::TYPE_PLACED) {
            $message = sprintf('%s placed an order for plan <b>%s</b>', $this->formatName($user), $plan->getName());
            $this->sendLog($message);
        }
        if ($event->getType() === OrderEvent::TYPE_PAID) {
            $message = sprintf('%s paid for plan <b>%s</b>', $this->formatName($user), $plan->getName());
            $this->sendLog($message);
        }
    }

    private function formatName(User $user): string
    {
        if (!$user->getTelegramRef()) {
            return sprintf('Site user <b>%s</b>', $user->getUsername());
        }

        $formattedName = sprintf('Telegram user <b>%s</b>', $user->getUsername());
        if ($user->getName()) {
            $formattedName .= sprintf(' ( @%s )', $user->getName());
        }

        return $formattedName;
    }

    private function sendLog(string $message): void
    {
        $this->logger->notice($message);
    }

    private function sendJobUpdates(UserSearch $search): void
    {
        $userUpdates = $this->upworkRequester->fetchUpdates($search->getSearchUrl());
        $userUpdates = (new FilterJobUpdatesFromStopWords())($search, $userUpdates);
        foreach ($userUpdates as $userUpdate) {
            // Create new UpworkJob
            $this->messageBus->dispatch(new SaveUpworkDataMessage($search, $userUpdate));
        }
        if (!empty($chatId = $search->getUser()->getTelegramRef())) {
            $this->telegramApi->sendBatchMessagesAsync((int)$chatId, $userUpdates, 15);
        }
    }
}
