<?php

namespace App\Application\TelegramBot\MessageHandler;

use App\Application\TelegramBot\Message\ListSearchesMessage;

class ListSearchesMessageHandler extends TelegramMessageHandler
{
    public function __invoke(ListSearchesMessage $message)
    {
        $user = $message->getUser();
        $searches = $user->getSearches();
        if (empty($searches->toArray())) {
            $text = 'You have no Upwork search links yet. Type "/add" to add one.';
        } else {
            $text = '';
            foreach ($searches as $id => $search) {
                if (!empty($search->getStopWords())) {
                    $stopWordsText = ' (stop words: '.implode(', ', $search->getStopWords()).')';
                } else {
                    $stopWordsText = '';
                }
                $text .= ($id + 1).'. '.$search->getSearchName().': '.$search->getSearchUrl().$stopWordsText.PHP_EOL;
            }
        }

        $keyboard = $this->telegramApi->buildHelpKeyboard($message->getUser());
        $this->telegramApi->sendMessage($user->getTelegramRef(), $text, $keyboard);
    }
}
