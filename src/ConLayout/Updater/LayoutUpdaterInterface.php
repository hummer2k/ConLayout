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
    const INSTRUCTION_LAYOUT_TEMPLATE = 'layout';
    const INSTRUCTION_BLOCKS          = 'blocks';
    const INSTRUCTION_REMOVE_BLOCKS   = 'remove_blocks';
    const INSTRUCTION_VIEW_HELPERS    = 'view_helpers';
    const INSTRUCTION_INCLUDE         = 'include';

    const AREA_GLOBAL   = 'global';
    const AREA_DEFAULT  = 'frontend';
    
    /**
     * Priority for the default handle.
     */
    const HANDLE_PRIORITY_DEFAULT = -1;

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
