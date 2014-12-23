<?php

namespace Knp\Rad\DoctrineEvent\Event;

use Doctrine\Common\EventArgs;
use Symfony\Component\EventDispatcher\GenericEvent;

class DoctrineEvent extends GenericEvent
{
    /**
     * @var EventArgs $parent
     */
    private $parent;

    /**
     * @var array $changeSet
     */
    private $changeSet;

    /**
     * @param object $subject
     * @param EventArgs $parent
     * @param array|null $arguments
     */
    public function __construct($subject, EventArgs $parent, array $arguments = array())
    {
        parent::__construct($subject, $arguments);

        $this->parent = $parent;
    }

    /**
     * @see getSubject
     * @return object
     */
    public function getEntity()
    {
        return $this->getSubject();
    }

    /**
     * @return EventArgs
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param EventArgs $parent
     *
     * @return DoctrineEvent
     */
    public function setParent(EventArgs $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get the entire changeset (or just chengeset of an attribute if key is setted)
     *
     * @param string|null $key
     *
     * @return array
     */
    public function getChangeSet($key = null)
    {
        $changeSet = $this->registerChangeSet();

        if (null === $key) {
            return $changeSet;
        }

        if (false === array_key_exists($key, $changeSet)) {
            return false;
        }

        return $changeSet[$key];
    }

    /**
     * Merge changeset of the given event into the current one
     *
     * @param DoctrineEvent $event
     *
     * @return null
     */
    public function merge(DoctrineEvent $event)
    {
        if ($this->subject !== $event->getSubject()) {
            throw new \Exception('Can\'t merge event from two differents instances.');
        }

        $existing = $this->registerChangeSet();
        $other    = $event->getChangeSet();

        foreach ($other as $key => $values) {
            if (false === array_key_exists($key, $existing)) {
                $existing[$key] = $values;

                continue;
            }
            if (end($existing[$key]) === current($values)) {
                array_shift($values);
            }
            $existing[$key] = array_merge($existing[$key], $values);
        }

        ksort($existing);
        $this->changeSet = $existing;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager()
    {
        return $this->parent->getEntityManager();
    }

    /**
     * @return UnitOfWork
     */
    public function getUnitOfWork()
    {
        return $this->getEntityManager()->getUnitOfWork();
    }

    /**
     * @return array
     */
    private function registerChangeSet()
    {
        return $this->changeSet = null === $this->changeSet
            ? $this->getUnitOfWork()->getEntityChangeSet($this->subject)
            : $this->changeSet
        ;
    }
}
