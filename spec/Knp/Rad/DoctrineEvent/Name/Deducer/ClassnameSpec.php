<?php

namespace spec\Knp\Rad\DoctrineEvent\Name\Deducer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ClassnameSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Knp\Rad\DoctrineEvent\Name\Deducer\Classname');
    }

    function it_compiles_event_name()
    {
        $this->deduce('App\\Entity\\User', 'prePersist')->shouldReturn('app.entity.user.pre_persist');
        $this->deduce('App\\Entity\\SuperUser', 'pre_persist')->shouldReturn('app.entity.super_user.pre_persist');
    }
}
