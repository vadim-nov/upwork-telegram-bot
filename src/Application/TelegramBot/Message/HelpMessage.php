<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 10/04/2019
 * Time: 13:40
 */

namespace App\Application\TelegramBot\Message;


class HelpMessage extends TelegramMessage
{
    private $isWithAnimation = false;

    public function getCommandPrefix(): string
    {
        return self::HELP_COMMAND;
    }

    public function getIsWithAnimation(): bool
    {
        return $this->isWithAnimation;
    }

    public function setIsWithAnimation(bool $bool): void
    {
        $this->isWithAnimation = $bool;
    }
}
