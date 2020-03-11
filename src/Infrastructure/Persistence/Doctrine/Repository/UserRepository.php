<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Core\Entity\Plan;
use App\Domain\Core\Entity\User;
use App\Domain\Core\Entity\UserSearch;
use App\Infrastructure\Persistence\UuidGenerator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    private $passwordEncoder;

    public function __construct(ManagerRegistry $registry, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        parent::__construct($registry, User::class);
    }

    public function add(User $user): void
    {
        $this->getEntityManager()->persist($user);
    }

    public function nextIdentity(): string
    {
        return UuidGenerator::generate();
    }

    /**
     * @param Plan|null $plan
     * @return User[]
     */
    public function findTelegramPlanSubscribers(Plan $plan = null): array
    {
        $queryBuilder = $this->createQueryBuilder('u');

        if ($plan) {
            $queryBuilder
                ->andWhere('u.currentPlan = :plan')
                ->setParameters(['plan' => $plan]);
        } else {
            $queryBuilder->andWhere('u.currentPlan IS NULL');
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function findByTelegramRef(string $ref): ?User
    {
        return $this->findOneBy(['telegramRef' => $ref]);
    }

    public function findByEmailOrUsername(string $username): ?User
    {
        return $this->createQueryBuilder('u')->andWhere('u.username=:username OR u.email=:username')->setParameter('username', $username)->getQuery()->getSingleResult();
    }

    public function findByBrowserExtLogin(string $username): ?User
    {
        return $this->findOneBy(['username' => $username]);
    }

    public function removeUserPendingSearches(User $user): void
    {
        $pendingSearches = $this->getEntityManager()->getRepository(UserSearch::class)
            ->findBy([
                'user' => $user,
                'isPending' => 1,
            ]);
        if (!empty($pendingSearches)) {
            foreach ($pendingSearches as $pendingSearch)
            $this->getEntityManager()->remove($pendingSearch);
        }
    }
}
