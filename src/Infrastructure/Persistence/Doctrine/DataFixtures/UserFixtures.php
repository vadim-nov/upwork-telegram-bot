<?php

namespace App\Infrastructure\Persistence\Doctrine\DataFixtures;

use App\Domain\Core\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture implements OrderedFixtureInterface
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        $faker->seed(101);//fixing data
        for ($i = 0; $i < 20; $i++) {
            $entity = User::createFromOauthProvider($faker->uuid, $faker->userName, $faker->email);
            $entity->hashPassword($this->encoder, $entity->getUsername());
            $manager->persist($entity);
            $this->addReference("user_{$i}", $entity);
        }
        $shopentity = User::createFromOauthProvider($faker->uuid, 'shop@example.com', 'shop@example.com');
        $shopentity->markAsDev();
        $shopentity->hashPassword($this->encoder, $shopentity->getUsername());
        $this->addReference("user_shop@example.com", $shopentity);
        $manager->persist($shopentity);
        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }

}
