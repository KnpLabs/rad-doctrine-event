<?php

namespace Knp\Rad\DoctrineEvent\Name\Deducer;

use Doctrine\Common\Inflector\Inflector;
use Knp\Rad\DoctrineEvent\Name\Deducer;

class Classname implements Deducer
{
    /**
     * {@inheritdoc}
     */
    public function deduce($class, $event)
    {
        $parts = explode('\\', $class);
        $parts = array_map(function ($e) { return Inflector::tableize($e); }, $parts);

        return sprintf('%s.%s', implode('.', $parts), Inflector::tableize($event));
    }
}
