<?php

declare(strict_types=1);

namespace App\Infrastructure\Integration;

class TelegramLoginCallback
{
    /**
     * Telegram ID of the user
     */
    private $userId;
    private $isValid;
    private $firstName;
    private $lastName;
    private $photoUrl;
    private $username;

    public function __construct(
        $id,
        bool $isValid,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $photoUrl = null,
        ?string $username = null
    ) {
        $this->userId = $id;
        $this->isValid = $isValid;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->photoUrl = $photoUrl;
        $this->username = $username;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getPhotoUrl(): ?string
    {
        return $this->photoUrl;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }
}