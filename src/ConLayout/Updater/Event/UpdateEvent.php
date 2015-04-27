<?php

namespace ConLayout\Updater\Event;

use Zend\Config\Config;
use Zend\EventManager\Event;

/**
 * @package ConLayout
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
     * @return Config
     */
    public function getLayoutStructure()
    {
        return $this->layoutStructure;
    }

    /**
     * @return array
     */
    public function getHandles()
    {
        return $this->handles;
    }

    /**
     * @param Config $layoutStructure
     * @return UpdateEvent
     */
    public function setLayoutStructure(Config $layoutStructure)
    {
        $this->layoutStructure = $layoutStructure;
        return $this;
    }

    /**
     * @param array $handles
     * @return UpdateEvent
     */
    public function setHandles(array $handles)
    {
        $this->handles = $handles;
        return $this;
    }
}
