<?php


namespace App\Infrastructure\Persistence\Doctrine\Repository;


use App\Domain\TelegramBot\Entity\TelegramMessageLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class TelegramMessageLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TelegramMessageLog::class);
    }

    public function findRecent(): array
    {
        return $this->findBy(
            [],
            ['createdAt' => 'DESC'],
            25
        );
    }


}
