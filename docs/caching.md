# Caching

## Cache the layout structure

Listen to the `UpdateEvent::EVENT_COLLECT` and `UpdateEvent::EVENT_COLLECT_POST` events:

### Example with Laminas\Cache:

```php
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
```

Attach the listener to the event manager of the layout updater. Use a
delegator factory for the `ConLayout\Updater\LayoutUpdaterInterface` service:

```php
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
```

## How the layout structure is built

ConLayout builds the layout structure once per request. The first call to
`getLayoutStructure()` collects the layout updates and merges them. Later
calls in the same request return the same structure.

The collection uses two events:

- `EVENT_COLLECT` fires before the collectors run. To use your own
  structure, return a `Config` object and stop the propagation. ConLayout
  then reads no layout update files.
- `EVENT_COLLECT_POST` fires after the collectors merged all updates. Use
  this event to save the structure to a cache.

The merged structure is read-only.

## Cache invalidation

ConLayout does not clear the cache. When you change a layout update file,
you must clear the affected cache items yourself.

The cache key must contain all values that select the structure. The
example above uses the handles and the area. Do not use the same cache key
for different areas.