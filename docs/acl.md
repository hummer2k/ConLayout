# ACL

An event is fired before the block instance is injected into the layout where
you can determine whether the block is allowed to be shown.

(return true or false in your event listener)

````php
<?php
namespace App\Listener;

use Laminas\EventManager\EventInterface;

class AclBlockListener
{
    public function __construct(private \Laminas\Permissions\Acl\Acl $acl)
    {
    }

    public function isAllowed(EventInterface $e): bool
    {
        $resource = $e->getParam('block_id');
        $e->stopPropagation();
        return $this->acl->isAllowed('user', $resource);
    }
}
````

The event is triggered on the `ConLayout\Layout\Layout` instance. Attach the
listener with a delegator factory for the layout service:

````php
<?php
namespace App\Factory;

use App\Listener\AclBlockListener;
use ConLayout\Layout\LayoutInterface;
use Psr\Container\ContainerInterface;

class LayoutAclDelegatorFactory
{
    public function __invoke(ContainerInterface $container, $name, callable $callback): mixed
    {
        $layout = $callback();
        $listener = $container->get(AclBlockListener::class);
        $layout->getEventManager()->attach('isAllowed', [$listener, 'isAllowed']);
        return $layout;
    }
}
````

````php
<?php
// in your application ConfigProvider
'dependencies' => [
    'factories' => [
        \App\Listener\AclBlockListener::class => \App\Listener\AclBlockListenerFactory::class
    ],
    'delegators' => [
        \ConLayout\Layout\LayoutInterface::class => [
            \App\Factory\LayoutAclDelegatorFactory::class
        ]
    ]
]
````
