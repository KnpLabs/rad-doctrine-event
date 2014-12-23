<?php

namespace Knp\Rad\DoctrineEvent\EventListener;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Knp\Rad\DoctrineEvent\Event\DoctrineEvent;
use Knp\Rad\DoctrineEvent\Name\Deducer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RedispatcherSubscriber implements EventSubscriber
{
    /**
     * @var EventDispatcherInterface $dispatcher
     */
    private $dispatcher;

    /**
     * @var Deducer $deducer
     */
    private $deducer;

    /**
     * @var array $cache
     */
    private $cache;

    /**
     * @param EventDispatcherInterface $dispatcher
     * @param Deducer $deducer
     */
    public function __construct(EventDispatcherInterface $dispatcher, Deducer $deducer)
    {
        $this->dispatcher = $dispatcher;
        $this->deducer    = $deducer;
        $this->cache      = [];
    }

    /**
     * @return array<*,string>
     */
    public function getSubscribedEvents()
    {
        return [
            Events::preRemove => 'preRemove',
            Events::postRemove => 'postRemove',
            Events::preUpdate => 'preUpdate',
            Events::postUpdate => 'postUpdate',
            Events::prePersist => 'prePersist',
            Events::postPersist => 'postPersist',
            Events::postLoad => 'postLoad',
        ];
    }

    /**
     * @param EventArgs $event
     *
     * @return null
     */
    public function preRemove(EventArgs $event)
    {
        $this->process($event, 'pre_remove', false);
    }

    /**
     * @param EventArgs $event
     *
     * @return null
     */
    public function postRemove(EventArgs $event)
    {
        $this->process($event, 'post_remove', true);
    }

    /**
     * @param EventArgs $event
     *
     * @return null
     */
    public function preUpdate(EventArgs $event)
    {
        $this->process($event, 'pre_update', false);
    }

    /**
     * @param EventArgs $event
     *
     * @return null
     */
    public function postUpdate(EventArgs $event)
    {
        $this->process($event, 'post_update', true);
    }

    /**
     * @param EventArgs $event
     *
     * @return null
     */
    public function prePersist(EventArgs $event)
    {
        $this->process($event, 'pre_persist', false);
    }

    /**
     * @param EventArgs $event
     *
     * @return null
     */
    public function postPersist(EventArgs $event)
    {
        $this->process($event, 'post_persist', true);
    }

    /**
     * @param EventArgs $event
     *
     * @return null
     */
    public function postLoad(EventArgs $event)
    {
        $this->process($event, 'post_load', true);
    }

    /**
     * @return null
     */
    public function onTerminate()
    {
        foreach ($this->cache as $name => $events) {
            foreach ($events as $event) {
                $entity   = $event->getEntity();
                $metadata = $this->getMetadata($entity, $event);
                $classes  = array_merge([ $metadata->getName() ], $metadata->parentClasses );

                foreach (array_reverse($classes) as $class) {
                    $this->notify($class, sprintf('%s_terminate', $name), $event);
                }
            }
        }
    }

    /**
     * Description
     *
     * @param EventArgs $event
     * @param string $name
     * @param boolean $onTerminate
     *
     * @return null
     */
    private function process($event, $name, $onTerminate)
    {
        $entity   = $event->getEntity();
        $metadata = $this->getMetadata($entity, $event);
        $newEvent = new DoctrineEvent($entity, $event);
        $classes  = array_merge([$metadata->getName()], $metadata->parentClasses );

        if (true === $onTerminate) {
            $this->registerTerminate($entity, $newEvent, $name);
        }

        foreach (array_reverse($classes) as $class) {
            $this->notify($class, $name, $newEvent);
        }
    }

    /**
     * @param string $class
     * @param string $name
     * @param DoctrineEvent $event
     *
     * @return null
     */
    private function notify($class, $name, DoctrineEvent $event)
    {
        $name = $this->deducer->deduce($class, $name);
        $this->dispatcher->dispatch($name, $event);
    }

    /**
     * @param object $entity
     * @param EventArgs|DoctrineEvent $event
     *
     * @return \Doctrine\ORM\Mapping\ClassMetadataInfo
     */
    private function getMetadata($entity, $event)
    {
        $em = $event->getEntityManager();

        return $em->getMetadataFactory()->getMetadataFor(get_class($entity));
    }

    /**
     * @param object $entity
     * @param DoctrineEvent $terminate
     * @param string $name
     *
     * @return null
     */
    private function registerTerminate($entity, DoctrineEvent $terminate, $name)
    {
        if (true === isset($this->cache[$name][spl_object_hash($entity)])) {
            $other = $this->cache[$name][spl_object_hash($entity)];
            $other->merge($terminate);
            $terminate = $other;
        }

        $terminate->getChangeSet();
        $this->cache[$name][spl_object_hash($entity)] = $terminate;
    }
}
