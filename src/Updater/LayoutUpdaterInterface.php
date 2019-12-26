<?php

namespace ConLayout\Updater;

use ConLayout\Handle\HandleInterface;
use Zend\Config\Config;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
interface LayoutUpdaterInterface
{
    public const INSTRUCTION_INCLUDE   = 'include';

    public const AREA_GLOBAL   = 'global';
    public const AREA_DEFAULT  = 'frontend';

    /**
     * retrieve layout structure for current request
     * respectively current handles
     *
     * @return Config
     */
    public function getLayoutStructure();

    /**
     * adds a handle
     *
     * @param HandleInterface $handle
     */
    public function addHandle(HandleInterface $handle);

    /**
     * set/replace handles
     *
     * @param array $handles
     */
    public function setHandles(array $handles);

    /**
     * removes a handle by name
     *
     * @param string $handleName
     */
    public function removeHandle($handleName);

    /**
     * retrieve handles
     *
     * @param bool $asObject
     * @return string[]|HandleInterface[]
     */
    public function getHandles($asObject = false);

    /**
     * set current area
     *
     * @param string $area
     */
    public function setArea($area);

    /**
     * retrieve current area
     *
     * @return string
     */
    public function getArea();
}
