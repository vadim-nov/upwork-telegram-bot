<?php


namespace App\Domain\TelegramBot\Exception;


use Throwable;

class SearchLimitReached extends TelegramEndUserException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}