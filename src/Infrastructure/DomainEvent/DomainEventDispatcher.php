<?php


namespace App\Infrastructure\DomainEvent;


use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Knp\Rad\DomainEvent\Dispatcher\Doctrine;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DomainEventDispatcher extends Doctrine
{
    private $disabled;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        parent::__construct($dispatcher);
    }

    public function disable()
    {
        $this->disabled = true;
    }

    public function enable()
    {
        $this->disabled = false;
    }

    public function postPersist(LifecycleEventArgs $event)
    {
        if ($this->disabled) {
            return;
        }
        parent::postPersist($event);
    }

    public function postLoad(LifecycleEventArgs $event)
    {
        if ($this->disabled) {
            return;
        }
        parent::postLoad($event);
    }

    public function postUpdate(LifecycleEventArgs $event)
    {
        if ($this->disabled) {
            return;
        }
        parent::postUpdate($event);
    }

    public function postRemove(LifecycleEventArgs $event)
    {
        if ($this->disabled) {
            return;
        }
        parent::postRemove($event);
    }

    public function postFlush(PostFlushEventArgs $event)
    {
        if ($this->disabled) {
            return;
        }
        parent::postFlush($event);
    }
}
