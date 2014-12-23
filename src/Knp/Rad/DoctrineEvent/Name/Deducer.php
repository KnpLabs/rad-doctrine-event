<?php

namespace Knp\Rad\DoctrineEvent\Name;

interface Deducer
{
    /**
     * @param string $class
     * @param string $event
     *
     * @return string
     */
    public function deduce($class, $event);
}
