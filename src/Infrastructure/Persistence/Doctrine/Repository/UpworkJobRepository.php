<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Core\Entity\UserSearch;
use App\Domain\Upwork\Entity\UpworkJob;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UpworkJob|null find($id, $lockMode = null, $lockVersion = null)
 * @method UpworkJob|null findOneBy(array $criteria, array $orderBy = null)
 * @method UpworkJob[]    findAll()
 * @method UpworkJob[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UpworkJobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UpworkJob::class);
    }

    public function markAllJobsAsRead()
    {
        $this->getEntityManager()->createQuery("UPDATE Upwork:UpworkJob j SET j.isRead=1")->execute();
    }

    public function cleanupOldJobs()
    {
        return $this->getEntityManager()->createQuery("DELETE from Upwork:UpworkJob j WHERE j.createdAt < :date")
            ->setParameter('date', Carbon::now()->modify('-5 days')->toDate())
            ->execute();
    }

    public function findBySearchAndLink(UserSearch $search, string $link): ?UpworkJob
    {
        return $this->findOneBy([
            'userSearch' => $search,
            'link' => $link,
        ]);
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
