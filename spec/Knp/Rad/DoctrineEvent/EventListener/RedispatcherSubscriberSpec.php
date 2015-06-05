<?php

namespace spec\Knp\Rad\DoctrineEvent\EventListener;

use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\UnitOfWork;
use Knp\Rad\DoctrineEvent\Name\Deducer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RedispatcherSubscriberSpec extends ObjectBehavior
{
    function let(EventDispatcherInterface $dispatcher, Deducer $deducer, LifecycleEventArgs $event, EntityManagerInterface $em, ClassMetadataFactory $factory, ClassMetadataInfo $metadata, UnitOfWork $uow, $entity)
    {
        $event->getEntity()->willReturn($entity);
        $event->getEntityManager()->willReturn($em);

        $em->getUnitOfWork()->willReturn($uow);
        $em->getMetadataFactory()->willReturn($factory);

        $factory->getMetadataFor(Argument::any())->willReturn($metadata);

        $metadata->getName()->willReturn('App\\Entity\Model');

        $deducer->deduce('App\\Entity\Model', 'post_persist')->willReturn('app.entity.model.post_persist');
        $deducer->deduce('App\\Entity\Model', 'pre_update')->willReturn('app.entity.model.pre_update');
        $deducer->deduce('App\\Entity\Model', 'post_persist_terminate')->willReturn('app.entity.model.post_persist_terminate');

        $this->beConstructedWith($dispatcher, $deducer, []);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Knp\Rad\DoctrineEvent\EventListener\RedispatcherSubscriber');
    }

    function it_dispatch_event($event, $dispatcher)
    {
        $dispatcher->dispatch('app.entity.model.post_persist', Argument::type('Knp\Rad\DoctrineEvent\Event\DoctrineEvent'))->shouldBeCalled();
        $this->postPersist($event);
    }

    function it_doesnt_dispatch_event_if_filtered($event, $dispatcher, $deducer)
    {
        $this->beConstructedWith($dispatcher, $deducer, ['App\\Entity\\Model']);

        $dispatcher->dispatch('app.entity.model.post_persist', Argument::type('Knp\Rad\DoctrineEvent\Event\DoctrineEvent'))->shouldNotBeCalled();

        $this->postPersist($event);
    }

    function it_dispatch_another_event($event, $dispatcher)
    {
        $dispatcher->dispatch('app.entity.model.pre_update', Argument::type('Knp\Rad\DoctrineEvent\Event\DoctrineEvent'))->shouldBeCalled();
        $this->preUpdate($event);
    }

    function it_dispatch_terminate_event_of_post_events($event, $dispatcher)
    {
        $this->postPersist($event);

        $dispatcher->dispatch('app.entity.model.post_persist_terminate', Argument::type('Knp\Rad\DoctrineEvent\Event\DoctrineEvent'))->shouldBeCalled();

        $this->onTerminate();
    }

    function it_doesnt_dispatch_terminate_event_of_pre_events($event, $dispatcher)
    {
        $this->preUpdate($event);

        $dispatcher->dispatch('app.entity.model.pre_update_terminate', Argument::type('Knp\Rad\DoctrineEvent\Event\DoctrineEvent'))->shouldNotBeCalled();

        $this->onTerminate();
    }
}
