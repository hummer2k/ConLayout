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
     *
     * @var string
     */
    protected $area;

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

    /**
     *
     * @return string
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     *
     * @param string $area
     * @return UpdateEvent
     */
    public function setArea($area)
    {
        $this->area = $area;
        return $this;
    }
}
