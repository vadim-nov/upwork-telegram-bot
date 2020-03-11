<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 10/04/2019
 * Time: 22:09
 */

namespace App\Application\TelegramBot\MessageHandler\Schedule;

use App\Application\TelegramBot\Message\Schedule\HelpLostTelegramUsersMessage;
use App\Domain\TelegramBot\Entity\TelegramMessageLog;
use App\Domain\TelegramBot\TelegramApiInterface;
use App\Infrastructure\Persistence\Doctrine\Repository\TelegramMessageLogRepository;
use App\Infrastructure\Persistence\Doctrine\Repository\UserRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class HelpLostTelegramUsersMessageHandler implements MessageHandlerInterface
{
    private $messageLogRepository;
    private $userRepository;
    private $telegramApi;

    public function __construct(
        TelegramMessageLogRepository $messageLogRepository,
        UserRepository $userRepository,
        TelegramApiInterface $telegramApi
    )
    {
        $this->messageLogRepository = $messageLogRepository;
        $this->userRepository = $userRepository;
        $this->telegramApi = $telegramApi;
    }

    public function __invoke(HelpLostTelegramUsersMessage $message)
    {
        $users = $this->userRepository->findAll();
        foreach ($users as $user) {
            /** @var TelegramMessageLog[] $userLogs */
            $userLogs = $this->messageLogRepository->findBy(['user' => $user], ['createdAt'=>'desc']);
            foreach ($userLogs as $log) {
                if (
                    (
                        false !== mb_strpos($log->getText(), 'Hey 👋')
                        || false !== mb_strpos($log->getText(), 'Nice one! Now you will be getting')
                    )
                    && false === $log->getIsInbound()
                ) {
                    break;
                }
                // last iteration
                if (false === next($userLogs)) {
                    $userLogs = array_filter($userLogs, function ($log) {
                        return true === $log->getIsInbound();
                    });
                    $newestLog = array_shift($userLogs);
                    $newestLogTime = $newestLog->getCreatedAt();
                    $newestLogTime->modify('+30 min');
                    if ($newestLogTime < new \DateTime('now')) {
                        try {
                            $this->telegramApi->sendMessage($user->getTelegramRef(), 'Hey 👋

we see you successfully started the bot 👍
but you didn\'t take any action further to get notifications about new upwork opportunities 🤔

we\'d love to know if you have any questions or need assistance to setup your jobs feed
Feel free to contact @telerodion 🙋‍♂️');
                        } catch (\Exception $e) {

                        }
                    }
                }
            }
        }
    }
}
