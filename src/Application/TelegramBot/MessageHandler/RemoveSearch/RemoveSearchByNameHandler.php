<?php

declare(strict_types=1);

namespace App\Application\TelegramBot\MessageHandler\RemoveSearch;

use App\Application\TelegramBot\Message\HelpMessage;
use App\Application\TelegramBot\MessageHandler\TelegramMessageHandler;
use App\Domain\Core\Entity\UserSearch;
use App\Application\TelegramBot\Message\RemoveSearch\RemoveSearchByName;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RemoveSearchByNameHandler extends TelegramMessageHandler implements MessageHandlerInterface
{
    public function __invoke(RemoveSearchByName $message): void
    {
        $user = $message->getUser();
        $searchId = $message->getSearchId();

        if ($message->isCancel()) {
            $this->telegramApi->removeMessage($user->getTelegramRef(), $message->getId());

            $this->messageBus->dispatch(new HelpMessage($message->getId(), $message->getMessage(), $message->getUser()));
            return;
        }
        $search = $this->entityManager->getRepository(UserSearch::class)->find($searchId);
        if (!$search) {
            $this->telegramApi->sendMessage($user->getTelegramRef(), 'Invalid search');

            return;
        }

        $user->removeSearch($search);
        $this->entityManager->flush();
        $text = sprintf('Search "%s" is successfully removed', $search->getSearchName());

        $keyboard = $this->telegramApi->buildHelpKeyboard($user);
        $this->telegramApi->sendMessage($user->getTelegramRef(), $text, $keyboard);
    }
}
