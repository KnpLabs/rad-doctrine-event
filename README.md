Rapid Application Development : Doctrine Events
===============================================
Access to your doctrine events from the Symfony DIC.

[![Build Status](https://travis-ci.org/KnpLabs/rad-doctrine-event.svg?branch=master)](https://travis-ci.org/KnpLabs/rad-doctrine-event)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/KnpLabs/rad-doctrine-event/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/KnpLabs/rad-doctrine-event/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/knplabs/rad-doctrine-event/v/stable)](https://packagist.org/packages/knplabs/rad-doctrine-event) [![Total Downloads](https://poser.pugx.org/knplabs/rad-doctrine-event/downloads)](https://packagist.org/packages/knplabs/rad-doctrine-event) [![Latest Unstable Version](https://poser.pugx.org/knplabs/rad-doctrine-event/v/unstable)](https://packagist.org/packages/knplabs/rad-doctrine-event) [![License](https://poser.pugx.org/knplabs/rad-doctrine-event/license)](https://packagist.org/packages/knplabs/rad-doctrine-event)

#Installation

```bash
composer require knplabs/rad-doctrine-event ~1.0@dev
```

```php
class AppKernel
{
    function registerBundles()
    {
        $bundles = array(
            //...
            new Knp\Rad\DoctrineEvent\Bundle\DoctrineEventBundle(),
            //...
        );

        //...

        return $bundles;
    }
}
```

#Usages

##Context

```php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class User
{
    //...
}
```

##Before

```php
namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;

class UserListener
{
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (false === $entity instanceof User) {
            return;
        }

        // Some stuff
    }
}
```

```yaml
#services.yml
services:
    app.event_listener.user_listener:
        class: App\EventListener\UserListener
        tags:
            - { name: doctrine.event_listener, event: pre_persist, method: prePersist }
```

##After

```php
namespace App\EventListener;

use Knp\Rad\DoctrineEvent\Event\DoctrineEvent;

class UserListener
{
    public function prePersist(DoctrineEvent $event)
    {
        $entity = $event->getEntity();

        // Some stuff
    }
}
```

```yaml
#services.yml
services:
    app.event_listener.user_listener:
        class: App\EventListener\UserListener
        tags:
            - { name: kernel.event_listener, event: app.entity.user.pre_persist, method: prePersist }
```

#Inheritance

##Context

```php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"page" = "App\Entity\Customer"})
 */
class User
{
    //...
}
```

```php
namespace App\Entity;

use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Customer extends User
{
    //...
}
```

##Events

Each event raised after handling an entity is preceded by the same event of the parent entity.

| Parent                      | Entity                          |
| --------------------------- | ------------------------------- |
| app.entity.user.pre_persist | app.entity.customer.pre_persist |
| app.entity.user.post_update | app.entity.customer.post_update |
| app.entity.user.post_load   | app.entity.customer.post_load   |
| ...                         | ...                             |

#Terminate

Each `post` (`post_persist`, `post_update`, `post_remove`, `post_load`) event is also redispatched during the `kernel.terminate` event.

| Event                        | Terminate event                        |
| ---------------------------- | -------------------------------------- |
| app.entity.user.post_persist | app.entity.user.post_persist_terminate |
| app.entity.user.post_update  | app.entity.user.post_update_terminate  |
| app.entity.user.post_remove  | app.entity.user.post_remove_terminate  |
| app.entity.user.post_load    | app.entity.user.post_load_terminate    |

#Configuration

You can restrict event re-dispatching to specific entities.

You just have to follow this configuration:

```yml
knp_rad_doctrine_event:
    entities:
        - MyBundle\Entity\User
```
