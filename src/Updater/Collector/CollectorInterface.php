<?php

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Updater\Collector;

use Laminas\Config\Config;

interface CollectorInterface
{
    /**
     * @param string $handle
     * @param null|string $area
     * @return Config
     */
    public function collect($handle, $area = null);
}
