<?php

namespace ConLayout\Updater\Event;

use Zend\Config\Config;
use Zend\EventManager\Event;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class FetchEvent extends Event
{
    /**
     *
     * @var string
     */
    protected $handle;

    /**
     *
     * @var Config
     */
    protected $layoutStructure;

    /**
     *
     * @return string
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     *
     * @param string $handle
     * @return FetchEvent
     */
    public function setHandle($handle)
    {
        $this->handle = $handle;
        return $this;
    }

    /**
     *
     * @return type
     */
    public function getLayoutStructure()
    {
        return $this->layoutStructure;
    }

    /**
     *
     * @param Config $layoutStructure
     * @return FetchEvent
     */
    public function setLayoutStructure(Config $layoutStructure)
    {
        $this->layoutStructure = $layoutStructure;
        return $this;
    }
}
