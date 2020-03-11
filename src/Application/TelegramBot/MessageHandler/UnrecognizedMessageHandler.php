<?php

namespace App\Application\TelegramBot\MessageHandler;

use App\Application\TelegramBot\Message\UnrecognizedMessage;

class UnrecognizedMessageHandler extends TelegramMessageHandler
{
    public function __invoke(UnrecognizedMessage $message)
    {
        if (!empty($message->getMessage())) {
            $text = 'Didn\'t quite catch that. Type "/help" to view all available commands.';

            $keyboard = $this->telegramApi->buildHelpKeyboard($message->getUser());
            $this->telegramApi->sendMessage($message->getUser()->getTelegramRef(), $text, $keyboard);
        }
    }
}
