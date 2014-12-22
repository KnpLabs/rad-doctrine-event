<?php

namespace Knp\Rad\DoctrineEvent\Bundle;

use Knp\Rad\DoctrineEvent\DependencyInjection\DoctrineEventExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DoctrineEventBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new DoctrineEventExtension();
    }
}
