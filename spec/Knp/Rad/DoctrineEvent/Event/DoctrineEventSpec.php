<?php

namespace spec\Knp\Rad\DoctrineEvent\Event;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\UnitOfWork;
use Knp\Rad\DoctrineEvent\Event\DoctrineEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DoctrineEventSpec extends ObjectBehavior
{
    function let(UnitOfWork $uow, EntityManagerInterface $em, LifecycleEventArgs $parent, $entity, DoctrineEvent $other)
    {
        $parent->getEntityManager()->willReturn($em);
        $em->getUnitOfWork()->willReturn($uow);
        $uow->getEntityChangeSet($entity)->willReturn(['field1' => ['old', 'new'], 'fiels3' => [1, 2]]);
        $other->getChangeSet()->willReturn(['field2' => ['e1', 'e2'], 'field1' => ['new', 'newest']]);
        $other->getSubject()->willReturn($entity);

        $this->beConstructedWith($entity, $parent);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Knp\Rad\DoctrineEvent\Event\DoctrineEvent');
    }

    function it_will_compute_changeset($uow, $entity)
    {
        $uow->getEntityChangeSet($entity)->shouldBeCalled();

        $this->getChangeSet()->shouldReturn(['field1' => ['old', 'new'], 'fiels3' => [1, 2]]);
    }

    function it_will_compute_chanset_once($uow, $entity)
    {
        $uow->getEntityChangeSet($entity)->shouldBeCalledTimes(1);

        $this->getChangeSet()->shouldReturn(['field1' => ['old', 'new'], 'fiels3' => [1, 2]]);
        $this->getChangeSet()->shouldReturn(['field1' => ['old', 'new'], 'fiels3' => [1, 2]]);
    }

    function it_merges_changeset($other)
    {
        $this->merge($other);

        $this->getChangeSet()->shouldReturn(['field1' => ['old', 'new', 'newest'], 'field2' => ['e1', 'e2'], 'fiels3' => [1, 2]]);
    }
}
