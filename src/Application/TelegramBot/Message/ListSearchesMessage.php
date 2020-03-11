<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 10/04/2019
 * Time: 13:40
 */

namespace App\Application\TelegramBot\Message;


class ListSearchesMessage extends TelegramMessage
{
    public function getCommandPrefix(): string
    {
        return self::LIST_SEARCH_COMMAND;
    }
}
