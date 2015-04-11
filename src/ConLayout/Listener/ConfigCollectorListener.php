<?php

namespace ConLayout\Listener;

use Zend\Config\Config;
use Zend\Config\Factory as ConfigFactory;
use Zend\EventManager\EventInterface;
use Zend\Stdlib\Glob;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ConfigCollectorListener
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
     * @param EventInterface $e
     */
    public function onLoadGlobalLayoutStructure(EventInterface $e)
    {
        /* @var $config Config */
        $layoutConfig = $e->getParam('global_layout_structure');
        foreach ($this->globPaths as $globPath) {
            foreach (Glob::glob($globPath, Glob::GLOB_BRACE) as $config) {
                $layoutConfig->merge(
                    ConfigFactory::fromFile($config, true)
                );
            }
        }
    }
}
