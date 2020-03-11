<?php

namespace App\Infrastructure\Persistence\Doctrine\DataFixtures;

use App\Domain\Core\Entity\User;
use App\Domain\TelegramBot\Entity\TelegramMessageLog;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class TelegramMessageFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        $faker->seed(101);//fixing data
        for ($i = 0; $i < 20; $i++) {
            /** @var User $user */
            $user = $this->getReference("user_$i");
            $entity = new TelegramMessageLog($user, $faker->text, false);
            $manager->persist($entity);
        }
        $manager->flush();
    }

    public function getOrder()
    {
        return 10;
    }

}
