<?php

declare(strict_types=1);

namespace App\Domain\Core\Event;

use App\Domain\Core\Entity\User;
use App\Domain\Core\Entity\UserSearch;
use Knp\Rad\DomainEvent\Event;

class UserEvent extends Event
{
    public const TYPE_SEARCH_ADDED = 'search_added';
    public const TYPE_SEARCH_REMOVED = 'search_removed';

    private $type;
    private $user;
    private $search;

    public function __construct(User $user, UserSearch $search, string $type)
    {
        parent::__construct(self::class);
        $this->user = $user;
        $this->search = $search;
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getSearch(): UserSearch
    {
        return $this->search;
    }
}