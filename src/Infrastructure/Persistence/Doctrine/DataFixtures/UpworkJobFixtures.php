<?php

namespace App\Infrastructure\Persistence\Doctrine\DataFixtures;

use App\Domain\Core\Entity\UserSearch;
use App\Domain\Upwork\Entity\UpworkJob;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class UpworkJobFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        for ($i = 0; $i < 20; $i++) {
            /** @var UserSearch $search */
            $search = $this->getReference('user_search_'.$i);
            $entity = new UpworkJob($faker->uuid, $search, $faker->url, $faker->url, $faker->word, $faker->realText(), $faker->dateTimeThisMonth());
            $manager->persist($entity);

        }
        /** @var UserSearch $search */
        $search = $this->getReference('user_search_shop@example.com');
        $entity = new UpworkJob($faker->uuid, $search, $faker->url, $faker->url, $faker->word, $faker->realText(),
            $faker->dateTimeThisMonth());
        $manager->persist($entity);
        $manager->flush();
    }

    public function getOrder()
    {
        return 3;
    }

}
