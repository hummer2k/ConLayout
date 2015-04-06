<?php

namespace ConLayout\Config;

use Zend\Config\Config;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
interface LayoutConfigInterface
{
    /**
     * @return Config
     */
    public function getBlockConfig();

    /**
     * @return Config
     */
    public function getCurrentLayoutConfig();
}
