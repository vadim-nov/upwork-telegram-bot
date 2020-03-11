<?php

declare(strict_types=1);

namespace App\Application\TelegramBot\Message\RemoveSearch;

use App\Domain\Core\Entity\User;
use App\Application\TelegramBot\Message\TelegramMessage;

class RemoveSearchByName extends TelegramMessage
{
    private $searchId;
    private $isCancel;

    public function __construct(
        int $messageId,
        string $message,
        User $user,
        string $searchId,
        bool $isCancel = false
    ) {
        parent::__construct($messageId, $message, $user);
        $this->searchId = $searchId;
        $this->isCancel = $isCancel;
    }

    public function getSearchId(): string
    {
        return $this->searchId;
    }

    public function isCancel(): bool
    {
        return $this->isCancel;
    }
}
