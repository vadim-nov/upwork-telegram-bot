<?php

namespace App\Infrastructure\Persistence\Doctrine\DataFixtures;

use App\Domain\Core\Entity\User;
use App\Domain\Core\Entity\UserSearch;
use App\Infrastructure\DomainEvent\DomainEventDispatcher;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Knp\Rad\DomainEvent\Dispatcher\Doctrine;
use Ramsey\Uuid\Uuid;

class UserSearchFixtures extends Fixture implements OrderedFixtureInterface
{
    private $domainEventsListener;
    public function __construct(DomainEventDispatcher $domainEventsListener)
    {
        $this->domainEventsListener = $domainEventsListener;
    }

    public function load(ObjectManager $manager)
    {
        $this->domainEventsListener->disable();
        $faker = Factory::create();
        for ($i = 0; $i < 20; $i++) {
            /** @var User $user */
            $user = $this->getReference("user_$i");
            $entity = new UserSearch(Uuid::uuid4()->toString(), $user, 'https://www.upwork.com/search/jobs/?q='.$faker->word, $faker->name);
            $manager->persist($entity);
            $this->addReference("user_search_{$i}", $entity);
        }
        /** @var User $ref */
        $ref = $this->getReference('user_shop@example.com');
        $entity = new UserSearch(Uuid::uuid4()->toString(), $ref, 'https://www.upwork.com/search/jobs/?q=react&sort=recency', $faker->name);
        $this->addReference("user_search_shop@example.com", $entity);

        $manager->persist($entity);
        $manager->flush();
        $this->domainEventsListener->enable();
    }

    public function getOrder()
    {
        return 2;
    }

}
