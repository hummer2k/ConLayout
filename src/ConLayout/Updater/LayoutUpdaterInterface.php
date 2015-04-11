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
    const APPLY_FOR = 'apply_for';
    
    /**
     * retrieve layout structure for current request respectively current handles
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
}
