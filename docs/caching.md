# Caching

## Caching layout structure

Listen to the `UpdateEvent::EVENT_COLLECT` and `UpdateEvent::EVENT_COLLECT_POST` events:

### Example with Zend\Cache:

````php
<?php

namespace Application\Listener;

use ConLayout\Updater\Event\UpdateEvent;
use Zend\Cache\Storage\StorageInterface;
use Zend\Config\Config;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;

class LayoutStructureCacheListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     *
     * @var StorageInterface
     */
    protected $cache;

    /**
     *
     * @param StorageInterface $cache
     */
    public function __construct(StorageInterface $cache)
    {
        $this->cache = $cache;
    }

    public function attach(EventManagerInterface $events)
    {
        $events->getSharedManager()->attach(
            'ConLayout\Updater\LayoutUpdater',
            UpdateEvent::EVENT_COLLECT,
            [$this, 'loadCache'],
            1000
        );
        $events->getSharedManager()->attach(
            'ConLayout\Updater\LayoutUpdater',
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