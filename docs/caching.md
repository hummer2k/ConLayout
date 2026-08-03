# Caching

## Caching layout structure

Listen to the `UpdateEvent::EVENT_COLLECT` and `UpdateEvent::EVENT_COLLECT_POST` events:

### Example with Laminas\Cache:

````php
<?php

namespace App\Listener;

use ConLayout\Updater\Event\UpdateEvent;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Config\Config;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;

class LayoutStructureCacheListener extends AbstractListenerAggregate
{
    public function __construct(private StorageInterface $cache)
    {
    }

    public function attach(EventManagerInterface $events, $priority = 1): void
    {
        // the layout updater triggers the events on its own event manager
        $this->listeners[] = $events->attach(
            UpdateEvent::EVENT_COLLECT,
            [$this, 'loadCache'],
            1000
        );
        $this->listeners[] = $events->attach(
            UpdateEvent::EVENT_COLLECT_POST,
            [$this, 'saveCache']
        );
    }

    protected function getCacheKey(array $handles, $area)
    {
        return md5(implode('|', $handles) . $area);
    }

    public function loadCache(UpdateEvent $e)
    {
        $handles = $e->getHandles();
        $cacheKey = $this->getCacheKey($handles, $e->getArea());
        if ($this->cache->hasItem($cacheKey)) {
            $e->stopPropagation();
            $layoutStructure = new Config(
                $this->cache->getItem($cacheKey)
            );
            return $layoutStructure;
        }
    }

    public function saveCache(UpdateEvent $e)
    {
        $handles = $e->getHandles();
        $cacheKey = $this->getCacheKey($handles, $e->getArea());
        $layoutStructure = $e->getLayoutStructure()->toArray();
        $this->cache->setItem($cacheKey, $layoutStructure);
    }
}
````

Attach the listener to the layout updater event manager via a delegator
factory for the `ConLayout\Updater\LayoutUpdaterInterface` service:

````php
<?php
namespace App\Factory;

use App\Listener\LayoutStructureCacheListener;
use ConLayout\Updater\LayoutUpdaterInterface;
use Psr\Container\ContainerInterface;

class LayoutUpdaterCacheDelegatorFactory
{
    public function __invoke(ContainerInterface $container, $name, callable $callback): mixed
    {
        $updater = $callback();
        $listener = $container->get(LayoutStructureCacheListener::class);
        $listener->attach($updater->getEventManager());
        return $updater;
    }
}
````