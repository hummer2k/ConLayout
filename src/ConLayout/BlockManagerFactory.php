<?php

namespace ConLayout;

use Zend\Mvc\Service\AbstractPluginManagerFactory;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockManagerFactory extends AbstractPluginManagerFactory
{
    const PLUGIN_MANAGER_CLASS = 'ConLayout\BlockManager';
}