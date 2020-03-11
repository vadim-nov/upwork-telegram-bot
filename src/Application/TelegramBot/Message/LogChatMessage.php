<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 14/05/2019
 * Time: 21:51
 */

namespace App\Application\TelegramBot\Message;


class LogChatMessage
{
    private $chatId;
    private $message;
    private $isInbound;

    public function __construct(int $chatId, string $message, bool $isInbound)
    {
        $this->chatId = $chatId;
        $this->message = $message;
        $this->isInbound = $isInbound;
    }

    public function getChatId(): int
    {
        return $this->chatId;
    }

    public function getChatMessage(): string
    {
        return $this->message;
    }

    public function isInbound(): bool
    {
        return $this->isInbound;
    }

}