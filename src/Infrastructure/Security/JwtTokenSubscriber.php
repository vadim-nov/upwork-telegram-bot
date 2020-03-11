<?php


namespace App\Infrastructure\Security;


use App\Domain\Core\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JwtTokenSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return ['lexik_jwt_authentication.on_jwt_created'=>'onJWTCreated'];
    }

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        /** @var User $user */
        $user = $event->getUser();
        $payload = $event->getData();
        $payload['userSearchesCount'] = $user->getSearches()->count();
        $payload['userPlan'] = $user->getCurrentPlan() ? $user->getCurrentPlan()->getName() : '';

        $event->setData($payload);
    }
}
