<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 10/04/2019
 * Time: 13:40
 */

namespace App\Application\TelegramBot\Message;


use App\Domain\Core\Entity\User;

abstract class TelegramMessage
{
    public const START_COMMAND = '/start';
    public const ADD_SEARCH_COMMAND = '/add';
    public const HELP_COMMAND = '/help';
    public const LIST_SEARCH_COMMAND = '/list';
    public const REMOVE_SEARCH_COMMAND = '/remove';
    public const PLAN = '/upgrade';

    private $user;
    private $message;
    private $id;

    public function __construct(int $id, string $message, User $user)
    {
        $this->id = $id;
        $this->message = $message;
        $this->user = $user;
    }

    public function getCommandPrefix(): string
    {
        return '';
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \App\Domain\Core\Entity\User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
