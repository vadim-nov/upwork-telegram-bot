<?php

declare(strict_types=1);

namespace App\Infrastructure\Integration;

class TelegramLoginChecker
{
    private $botApiToken;

    public function __construct(string $botApiToken)
    {
        $this->botApiToken = $botApiToken;
    }

    public function parseLoginCallback(array $callbackData): TelegramLoginCallback
    {
        $telegramHash = $callbackData['hash'];
        unset($callbackData['hash']);

        $dataCheckArray = [];
        foreach ($callbackData as $key => $value) {
            $dataCheckArray[] = $key . '=' . $value;
        }
        sort($dataCheckArray);
        $dateCheckString = implode("\n", $dataCheckArray);
        $telegramBotTokenHash = hash('sha256', $this->botApiToken, true);
        $calculatedHash = hash_hmac('sha256', $dateCheckString, $telegramBotTokenHash);

        return new TelegramLoginCallback(
            $callbackData['id'],
            $telegramHash === $calculatedHash,
            $callbackData['first_name'] ?? null,
            $callbackData['last_name'] ?? null,
            $callbackData['photo_url'] ?? null,
            $callbackData['username'] ?? null
        );
    }
}