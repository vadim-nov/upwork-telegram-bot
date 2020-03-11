<?php

namespace App\Infrastructure\Persistence\Doctrine\DataFixtures;

use App\Domain\Core\Entity\Plan;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Money\Currency;
use Money\Money;

class PlanFixtures extends Fixture implements OrderedFixtureInterface
{


    public function load(ObjectManager $manager)
    {
        $standart = new Plan('2', Plan::PLAN_STANDARD, new Money(500, new Currency('USD')), 2, 10);
        $prem = new Plan('3', Plan::PLAN_PREMIUM, new Money(1000, new Currency('USD')), 3, 1);
        $manager->persist($standart);
        $manager->persist($prem);
        $manager->flush();
    }

    public function getOrder()
    {
        return 5;
    }

}
