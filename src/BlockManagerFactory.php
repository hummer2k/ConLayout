<?php
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout;

use Zend\Mvc\Service\AbstractPluginManagerFactory;

class BlockManagerFactory extends AbstractPluginManagerFactory
{
    const PLUGIN_MANAGER_CLASS = 'ConLayout\BlockManager';
}
