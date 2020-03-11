<?php

namespace App\Application\TelegramBot\MessageHandler\AddSearch;

use App\Application\TelegramBot\Message\HelpMessage;
use App\Domain\Core\Entity\User;
use App\Application\TelegramBot\Message\AddSearch\AddSearchLink;
use App\Application\TelegramBot\MessageHandler\TelegramMessageHandler;
use App\Domain\Upwork\SearchUrlParser;
use App\Domain\Upwork\ValueObject\UpworkSearchFilter;

class AddSearchLinkHandler extends TelegramMessageHandler
{
    public function __invoke(AddSearchLink $message)
    {
        $user = $message->getUser();
        $workflow = $this->workflows->get($user);
        if ('/cancel' === $message->getMessage()) {
            $this->entityManager->getRepository(User::class)->removeUserPendingSearches($user);
            $this->entityManager->flush();

            $workflow->apply($user, 'add_seach_cancel');
            $this->telegramApi->removeMessage($user->getTelegramRef(), $message->getId());

            $this->messageBus->dispatch(new HelpMessage($message->getId(), $message->getMessage(), $message->getUser()));
        } else {
            try {
                $searchUrl = $message->getMessage();
                $searchUrlParser = new SearchUrlParser();
                $searchUrlParser->assertValidSearchUrl($searchUrl);
                $user->addSearchPending($this->entityManager->getRepository(User::class)->nextIdentity(), $searchUrl);
                $this->entityManager->flush();

                $workflow->apply($user, 'add_search_link');

                $text = 'Now, give it a name, please.';
                $buttons[] = [['callback_data' => '/cancel', 'text' => 'Cancel']];

                $keyboard = [
                    'inline_keyboard' => $buttons,
                ];
                $this->telegramApi->sendMessage($user->getTelegramRef(), $text, $keyboard);
            } catch (\DomainException $endUserException) {
                $buttons[] = [['callback_data' => '/cancel', 'text' => 'Cancel']];

                $keyboard = [
                    'inline_keyboard' => $buttons,
                ];
                $this->telegramApi->sendMessage($user->getTelegramRef(), $endUserException->getMessage(), $keyboard);
            }
        }
    }
}
