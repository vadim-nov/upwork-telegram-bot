<?php

namespace App\Application\TelegramBot\MessageHandler\AddSearch;

use App\Application\TelegramBot\Message\AddSearch\AddSearchStopWords;
use App\Application\TelegramBot\Message\HelpMessage;
use App\Application\TelegramBot\MessageHandler\TelegramMessageHandler;
use App\Domain\Core\Entity\User;

class AddSearchStopWordsHandler extends TelegramMessageHandler
{
    public function __invoke(AddSearchStopWords $message)
    {
        $user = $message->getUser();
        $workflow = $this->workflows->get($user);
        if ('/cancel' === $message->getMessage()) {
            $this->entityManager->getRepository(User::class)->removeUserPendingSearches($user);
            $this->entityManager->flush();

            $workflow->apply($user, 'add_seach_cancel');
            $this->telegramApi->removeMessage($user->getTelegramRef(), $message->getId());

            $this->messageBus->dispatch(new HelpMessage($message->getId(), $message->getMessage(), $message->getUser()));
            return;
        }
        if (!empty($message->getMessage()) && '/skip' !== $message->getMessage()) {
            $stopWords = [];
            foreach (explode(',', $message->getMessage()) as $stopWord) {
                $stopWords[] = trim($stopWord);
            }
        }

        $keyboard = $this->telegramApi->buildHelpKeyboard($message->getUser());

        $pendingSearch = $user->getPendingSearch();
        if (null === $pendingSearch) {
            $workflow->apply($user, 'add_seach_cancel');
            $text = 'Sorry, something went wrong :('.PHP_EOL.'Please try adding new search again.';
            $this->telegramApi->sendMessage($user->getTelegramRef(), $text, $keyboard);
            return;
        }

        $pendingSearch->completeCreation($user, $stopWords ?? []);

        $text = 'Nice one! Now you will be getting notifications about such upwork jobs.';
        $this->telegramApi->sendMessage($user->getTelegramRef(), $text, $keyboard);

        $this->entityManager->flush();
        $workflow->apply($user, 'add_search_stop_words');
        $workflow->apply($user, 'add_search_finish');
    }
}
