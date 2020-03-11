<?php

namespace App\Application\TelegramBot\MessageHandler\AddSearch;

use App\Domain\Core\Exception\SearchLimitReachedException;
use App\Application\TelegramBot\Message\AddSearch\AddSearchStart;
use App\Application\TelegramBot\MessageHandler\TelegramMessageHandler;

class AddSearchStartHandler extends TelegramMessageHandler
{
    public function __invoke(AddSearchStart $message)
    {
        $user = $message->getUser();
        $workflow = $this->workflows->get($user);
        try {
            $user->assertCanAddSearch();
        } catch (SearchLimitReachedException $exception) {
            $searchCount = $user->getSearches()->count();
            $text = sprintf('Oh, you can have only %s search link%s, remove one to replace with something else. Or get supernatural power with our /upgrade.', $searchCount, $searchCount === 1 ? '' : 's');
            $keyboard = $this->telegramApi->buildHelpKeyboard($message->getUser());
            $this->telegramApi->sendMessage($user->getTelegramRef(), $text, $keyboard);
            return;
        }

        $workflow->apply($user, 'add_new_search');
        $text = 'Paste the upwork search URL or just enter a keyword.'
            .PHP_EOL.'Press Enter to submit the message.';
        $buttons[] = [['callback_data' => '/cancel', 'text' => 'Cancel']];
        $keyboard = ['inline_keyboard' => $buttons];
        $this->telegramApi->sendMessage($user->getTelegramRef(), $text, $keyboard);
    }
}
