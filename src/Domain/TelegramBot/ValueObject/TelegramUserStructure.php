<?php


namespace App\Domain\TelegramBot\ValueObject;


class TelegramUserStructure
{
    private $username;
    private $first_name;
    private $last_name;
    private $id;

    public function __construct(int $id, array $profile = [])
    {
        $this->id = $id;
        $this->completeProfile($profile);
    }

    public function completeProfile(array $profile)
    {
        $this->username = isset($profile['username']) ? $profile['username'] : null;
        $this->first_name = isset($profile['first_name']) ? $profile['first_name'] : null;
        $this->last_name = isset($profile['last_name']) ? $profile['last_name'] : null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function __toString()
    {
        if ($this->username) {
            return $this->username;
        } else {
            return (string)$this->id;
        }
    }
}
