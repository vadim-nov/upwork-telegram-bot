<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 10/04/2019
 * Time: 22:09
 */

namespace App\Application\TelegramBot\Message\Schedule;

class NotificationJobMessage
{
    private $plan;

    public function __construct(string $plan = null)
    {
        $this->plan = $plan;
    }

    public function getPlan(): ?string
    {
        return $this->plan;
    }
}
