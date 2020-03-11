<?php

namespace App\Application\TelegramBot\MessageHandler;

use App\Application\TelegramBot\Message\HelpMessage;

class HelpMessageHandler extends TelegramMessageHandler
{
    public function __invoke(HelpMessage $message)
    {
        $text = 'Here is the list of all available commands:'.PHP_EOL
            .'/help - View help message'.PHP_EOL
            .'/add - Add new Upwork search link to get updates on.'.PHP_EOL
            .'/list - List all your Upwork search links'.PHP_EOL
            .'/remove - Remove Upwork search link';

        $keyboard = $this->telegramApi->buildHelpKeyboard($message->getUser());
        $this->telegramApi->sendMessage($message->getUser()->getTelegramRef(), $text, $keyboard);
        if ($message->getIsWithAnimation()) {
            $this->telegramApi->sendHelpAnimation($message->getUser()->getTelegramRef());
        }
    }
}
