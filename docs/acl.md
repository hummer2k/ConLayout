# ACL

ConLayout fires an event before it injects the block instance into the layout.
In this event, you can determine whether the block is allowed to be shown.

Your event listener must return `true` or `false`.

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
        $resource = $e->getParam('blockId');
        $e->stopPropagation();
        return $this->acl->isAllowed('user', $resource);
    }
}
````

ConLayout triggers the event on the `ConLayout\Layout\Layout` instance. Attach
the listener with a delegator factory for the layout service:

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

## How the event works

The event name is `isAllowed`. ConLayout triggers it on the event manager
of the `Layout` instance during `injectBlocks()`. The event has these
parameters:

- `blockId`: The id of the block.
- `block`: The view model instance of the block.

ConLayout allows the block when no listener stops the propagation. To deny
a block, stop the propagation and return `false`. ConLayout does not add a
denied block to the rendered tree.

The `root` block is never checked. ConLayout always renders the root.

## Other layout events

The `Layout` instance triggers two events during `load()`:

- `load.pre` before the generators run.
- `load.post` after ConLayout injected the blocks.

The `BlockFactory` triggers `configure.post` after it configures a block.
A listener can return another view model to replace the block.
