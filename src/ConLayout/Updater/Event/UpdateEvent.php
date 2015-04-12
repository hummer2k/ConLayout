<?php

namespace ConLayout\Updater\Event;

use Zend\Config\Config;
use Zend\EventManager\Event;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class UpdateEvent extends Event
{
    /**
     * @var Config
     */
    protected $globalLayoutStructure;

    /**
     *
     * @var Config
     */
    protected $layoutStructure;

    /**
     *
     * @var array
     */
    protected $handles;

    /**
     * @return Config
     */
    public function getGlobalLayoutStructure()
    {
        return $this->globalLayoutStructure;
    }

    /**
     * @param Config $globalLayoutStructure
     * @return UpdateEvent
     */
    public function setGlobalLayoutStructure(Config $globalLayoutStructure)
    {
        $this->globalLayoutStructure = $globalLayoutStructure;
        return $this;
    }

    /**
     *
     * @codeCoverageIgnore
     * @return Config
     */
    public function getLayoutStructure()
    {
        return $this->layoutStructure;
    }

    /**
     *
     * @codeCoverageIgnore
     * @return array
     */
    public function getHandles()
    {
        return $this->handles;
    }

    /**
     * @codeCoverageIgnore
     * @param Config $layoutStructure
     * @return UpdateEvent
     */
    public function setLayoutStructure(Config $layoutStructure)
    {
        $this->layoutStructure = $layoutStructure;
        return $this;
    }

    /**
     * @codeCoverageIgnore
     * @param array $handles
     * @return UpdateEvent
     */
    public function setHandles(array $handles)
    {
        $this->handles = $handles;
        return $this;
    }
}
