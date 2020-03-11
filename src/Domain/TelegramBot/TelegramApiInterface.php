<?php


namespace App\Domain\TelegramBot;

use App\Domain\Core\Entity\User;
use GuzzleHttp\Exception\ClientException;

interface TelegramApiInterface
{
    /**
     * @param int $chatId
     * @param string $text
     * @param array $keyboard
     * @throws ClientException
     */
    public function sendMessage(int $chatId, string $text, array $keyboard = []): void;

    public function sendBatchMessagesAsync(int $chatId, array $messages, int $chunkSize): void;

    public function removeMessage(int $chatId, int $messageId): void;

    public function sendHelpAnimation(int $chatId): void;

    public function buildHelpKeyboard(User $user): array;
}