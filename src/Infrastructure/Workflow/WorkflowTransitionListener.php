<?php

namespace App\Infrastructure\Workflow;

use App\Domain\Core\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowTransitionListener implements EventSubscriberInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.telegram_user_search_adding.entered' => 'onEntered',
        ];
    }

    public function onEntered(Event $event)
    {
        if (!$event->getSubject() instanceof User) {
            return;
        }
        $this->entityManager->flush();
    }
}
