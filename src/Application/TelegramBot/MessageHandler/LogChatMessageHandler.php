<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 14/05/2019
 * Time: 22:02
 */

namespace App\Application\TelegramBot\MessageHandler;


use App\Application\TelegramBot\Message\LogChatMessage;
use App\Domain\TelegramBot\Entity\TelegramMessageLog;
use App\Infrastructure\Persistence\Doctrine\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class LogChatMessageHandler implements MessageHandlerInterface
{
    private $entityManager;
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    public function __invoke(LogChatMessage $message)
    {
        $user = $this->userRepository->findByTelegramRef($message->getChatId());
        if (!$user) {
            throw new \RuntimeException('No user with chat id: '.$message->getChatId());
        }
        $logEntry = new TelegramMessageLog(
            $user,
            $message->getChatMessage(),
            $message->isInbound()
        );

        $this->entityManager->persist($logEntry);
        $this->entityManager->flush();
    }

}