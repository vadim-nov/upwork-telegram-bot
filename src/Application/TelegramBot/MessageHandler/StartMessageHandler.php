<?php

namespace App\Application\TelegramBot\MessageHandler;

use App\Application\TelegramBot\DomainEventSubscriber\ActivityEventSubscriber;
use App\Application\TelegramBot\Message\StartMessage;
use App\Domain\TelegramBot\TelegramApiInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class StartMessageHandler implements MessageHandlerInterface
{
    private $activitySubscriber;
    private $telegramApi;

    public function __construct(TelegramApiInterface $telegramApi, ActivityEventSubscriber $activitySubscriber)
    {
        $this->activitySubscriber = $activitySubscriber;
        $this->telegramApi = $telegramApi;
    }

    public function __invoke(StartMessage $message)
    {
        $text = 'I\'m Upwork bot.'.PHP_EOL
            .'/help - View help message'.PHP_EOL
            .'/add - Add new Upwork search link to get updates on.'.PHP_EOL
            .'/list - List all your Upwork search links'.PHP_EOL
            .'/remove - Remove Upwork search link';

        $keyboard = $this->telegramApi->buildHelpKeyboard($message->getUser());
        $this->telegramApi->sendMessage($message->getUser()->getTelegramRef(), $text, $keyboard);
        $this->telegramApi->sendHelpAnimation($message->getUser()->getTelegramRef());
        $this->activitySubscriber->onBotStarted($message->getUser());
    }
}
