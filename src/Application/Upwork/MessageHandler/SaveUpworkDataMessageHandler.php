<?php

namespace App\Application\Upwork\MessageHandler;

use App\Domain\Upwork\Entity\UpworkJob;
use App\Application\Upwork\Message\SaveUpworkDataMessage;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SaveUpworkDataMessageHandler implements MessageHandlerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(SaveUpworkDataMessage $message)
    {
        $job = UpworkJob::fromUpworkDataView(
            Uuid::uuid4()->toString(),
            $message->getUserSearch(),
            $message->getUpworkDataView()
        );
        $this->entityManager->persist($job);
        $this->entityManager->flush();
    }
}
