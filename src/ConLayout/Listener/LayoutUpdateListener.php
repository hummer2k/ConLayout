<?php

namespace ConLayout\Listener;

use ConLayout\Updater\Event\UpdateEvent;
use Zend\Config\Factory as ConfigFactory;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Stdlib\Glob;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutUpdateListener implements ListenerAggregateInterface
{
    use \Zend\EventManager\ListenerAggregateTrait;

    /**
     *
     * @var array
     */
    protected $globPaths;

    /**
     *
     * @param array|string $globPaths
     */
    public function __construct($globPaths)
    {
        if (!is_array($globPaths)) {
            $globPaths = [$globPaths];
        }
        $this->globPaths = $globPaths;
    }

    /**
     *
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $events->getSharedManager()->attach(
            'ConLayout\Updater\LayoutUpdater',
            'loadGlobalLayoutStructure.pre',
            [$this, 'onLoadGlobalLayoutStructure']
        );
    }

    /**
     *
     * @param UpdateEvent $e
     */
    public function onLoadGlobalLayoutStructure(UpdateEvent $e)
    {
        $globalLayoutStructure = $e->getGlobalLayoutStructure();
        foreach ($this->globPaths as $globPath) {
            foreach (Glob::glob($globPath, Glob::GLOB_BRACE) as $config) {
                $globalLayoutStructure->merge(
                    ConfigFactory::fromFile($config, true)
                );
            }
        }
    }
}
