<?php

declare(strict_types=1);

namespace ConLayout;

use Laminas\Mvc\Service\AbstractPluginManagerFactory;

class BlockManagerFactory extends AbstractPluginManagerFactory
{
    public const PLUGIN_MANAGER_CLASS = 'ConLayout\BlockManager';
}
