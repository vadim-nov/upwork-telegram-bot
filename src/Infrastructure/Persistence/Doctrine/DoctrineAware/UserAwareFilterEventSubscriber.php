<?php
/**
 * Created by PhpStorm.
 * User: mitalcoi
 * Date: 04.03.2018
 * Time: 20:40
 */

namespace App\Infrastructure\Persistence\Doctrine\DoctrineAware;

use App\Domain\Core\Entity\User;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Security\Core\Security;

final class UserAwareFilterEventSubscriber implements EventSubscriberInterface
{
    private $em;
    private $security;
    private $reader;

    public function __construct(
        EntityManagerInterface $em,
        Security $security,
        Reader $reader
    ) {
        $this->em = $em;
        $this->security = $security;
        $this->reader = $reader;
    }

    public static function getSubscribedEvents()
    {
        return ['kernel.request' => ['onKernelView', 5]];
    }

    public function onKernelView(RequestEvent $event): void
    {
        $user = $this->security->getUser();
        if ('easyadmin' === $event->getRequest()->attributes->get('_route')) {
            return;
        }
        if ($user instanceof User) {
            $filter = $this->em->getFilters()->enable('user_filter');
            $filter->setParameter('id', $user->getId());
            $filter->setAnnotationReader($this->reader);
        }
    }
}
