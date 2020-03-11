<?php


namespace App\Tests\Infrastructure\Integration;


use App\Domain\Core\Entity\User;
use App\Domain\TelegramBot\TelegramApiInterface;

class TelegramApiMock implements TelegramApiInterface
{
    public function sendMessage(int $chatId, string $text, array $keyboard = []): void
    {
        $dir = __DIR__.'/../../../var/telegram_messages/';
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        file_put_contents($dir.$chatId.'.message', $text);
    }

    public function sendBatchMessagesAsync(int $chatId, array $messages, int $chunkSize): void
    {
        $dir = __DIR__.'/../../../var/telegram_messages/';
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        foreach ($messages as $id => $message) {
            file_put_contents($dir.$id.'.message', $message->getDescription());
        }
    }

    public function removeMessage(int $chatId, int $messageId): void
    {
    }

    public function buildHelpKeyboard(User $user): array
    {
        return [];
    }

    public function sendHelpAnimation(int $chatId): void
    {
    }

    public function buildKeyboardDuringAdding(User $user): array
    {
        return [];
    }
}