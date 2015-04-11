<?php

namespace ConLayout\Listener;

use ConLayout\Updater\Event\UpdateEvent;
use Zend\Config\Factory as ConfigFactory;
use Zend\EventManager\EventInterface;
use Zend\Stdlib\Glob;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutUpdateListener
{
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
