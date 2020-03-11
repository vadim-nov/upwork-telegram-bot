<?php

namespace App\Ui\Twig;

use App\Domain\Core\Entity\Plan;
use App\Domain\Core\Entity\User;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UserPlansExtension {

    public function userPlans(Environment $environment)
    {
        /** @var User|null $user */
        $user = null;
        $plans = [
            [
                'name' => 'Starter',
                'pic' => '/static/img/bike.svg',
                'price' => 'FREE',
                'searches' => '1 search',
                'search_title' => 'Every 15 mins update',
                'is_current' => $user && !$user->isPremiumUser(),
            ],
            [
                'name' => Plan::PLAN_STANDARD,
                'pic' => '/static/img/scooter.svg',
                'price' => '$5<span>/Month</span>',
                'searches' => '2 searches',
                'search_title' => 'Every 5 mins update',
                'is_current' => $user && $user->isPremiumUser() && $user->getCurrentPlan()->getName() === 'Standard'
                //@TODO: need to refactor
            ],
            [
                'name' => Plan::PLAN_PREMIUM,
                'pic' => '/static/img/car.svg',
                'price' => '$10<span>/Month</span>',
                'searches' => '3 searches',
                'search_title' => 'Every minute update',
                'is_current' => $user && $user->isPremiumUser() && $user->getCurrentPlan()->getName() === 'Premium'
                //@TODO: need to refactor
            ],
        ];
        if ($user && $user->isPremiumUser()) {
            unset($plans[0]);
        }
    }
}
