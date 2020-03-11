<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Core\Entity\Plan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Plan|null find($id, $lockMode = null, $lockVersion = null)
 * @method Plan|null findOneBy(array $criteria, array $orderBy = null)
 * @method Plan[]    findAll()
 * @method Plan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plan::class);
    }

    public function findOneByName(string $name): ?Plan
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * @inheritDoc
     */
    public function findAllFiltered(bool $onlyVisible = false): array
    {
        if ($onlyVisible) {
            return $this->findBy(['isVisible' => true]);
        }
        return $this->findAll();
    }
}
