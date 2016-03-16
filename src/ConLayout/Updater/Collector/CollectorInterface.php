<?php
/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Updater\Collector;


use Zend\Config\Config;

interface CollectorInterface
{
    /**
     * @param array $handles
     * @return mixed
     */
    public function init(array $handles);

    /**
     * @param string $handle
     * @return mixed
     */
    public function fetchHandle($handle);

    /**
     * @param string $area
     * @return mixed
     */
    public function setArea($area);
}
