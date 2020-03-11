<?php

namespace App\Application\TelegramBot\MessageHandler\AddSearch;

use App\Application\TelegramBot\Message\AddSearch\AddSearchName;
use App\Application\TelegramBot\Message\HelpMessage;
use App\Application\TelegramBot\MessageHandler\TelegramMessageHandler;
use App\Domain\Core\Entity\User;

class AddSearchNameHandler extends TelegramMessageHandler
{
    public function __invoke(AddSearchName $message)
    {
        $user = $message->getUser();
        $searchName = $message->getMessage();
        $workflow = $this->workflows->get($user);
        if ('/cancel' === $message->getMessage()) {
            $this->entityManager->getRepository(User::class)->removeUserPendingSearches($user);
            $this->entityManager->flush();

            $workflow->apply($user, 'add_seach_cancel');
            $this->telegramApi->removeMessage($user->getTelegramRef(), $message->getId());

            $this->messageBus->dispatch(new HelpMessage($message->getId(), $message->getMessage(), $message->getUser()));

            return;
        }
        if (empty($searchName)) {
            $text = 'Please provide a name for Upwork search setting.';
            $keyboard = $this->telegramApi->buildHelpKeyboard($message->getUser());
            $this->telegramApi->sendMessage($user->getTelegramRef(), $text, $keyboard);
            return;
        }
        if ($user->hasSearch($searchName)) {
            $this->entityManager->getRepository(User::class)->removeUserPendingSearches($user);
            $this->entityManager->flush();

            $workflow->apply($user, 'add_seach_cancel');

            $text = sprintf('Search <b>%s</b> already exists.', $searchName);
            $keyboard = $this->telegramApi->buildHelpKeyboard($message->getUser());
            $this->telegramApi->sendMessage($user->getTelegramRef(), $text, $keyboard);
            return;
        }

        $pendingSearch = $user->getPendingSearch();
        $pendingSearch->setName($searchName);

        $text = 'Would you like to filter out job posts that include specific stop words? Please, list them separated by comma. Example: lowest bids, urgently, wordpress.'
        .PHP_EOL.'Press Enter to submit the message.';
        $buttons[] = [['callback_data' => '/skip', 'text' => 'Skip']];
        $buttons[] = [['callback_data' => '/cancel', 'text' => 'Cancel']];
        $keyboard = ['inline_keyboard' => $buttons];
        $this->telegramApi->sendMessage($user->getTelegramRef(), $text, $keyboard);

        $workflow->apply($user, 'add_search_name');
    }
}
