<?php

declare(strict_types=1);

namespace App\Application\TelegramBot\MessageHandler\RemoveSearch;

use App\Domain\Core\Entity\UserSearch;
use App\Application\TelegramBot\Message\RemoveSearch\RemoveSearchRequested;
use App\Application\TelegramBot\MessageHandler\TelegramMessageHandler;

class RemoveSearchRequestedHandler extends TelegramMessageHandler
{
    public function __invoke(RemoveSearchRequested $message)
    {
        $user = $message->getUser();
        $searches = $user->getSearches();

        if ($searches->isEmpty()) {
            $this->telegramApi->sendMessage($user->getTelegramRef(),
                'You don\'t have any subscriptions yet. Type "/add" to add one');

            return;
        }

        foreach ($searches as $key => $search) {
            if ($search->isPending()) {
                $this->entityManager->remove($search);
            } else {
                $buttons[] = [
                    [
                        'callback_data' => sprintf('/remove %s', $search->getId()),
                        'text' => $search->getSearchName(),
                    ],
                ];
            }
        }

        $this->entityManager->flush();
        $buttons[] = [['callback_data' => '/remove cancel', 'text' => 'Cancel']];

        $keyboard = [
            'inline_keyboard' => $buttons,
            'one_time_keyboard' => true,
        ];

        $this->telegramApi->sendMessage($user->getTelegramRef(), 'Select search to remove', $keyboard);
    }
}
