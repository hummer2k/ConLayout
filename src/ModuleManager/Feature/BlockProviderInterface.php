<?php

namespace ConLayout\ModuleManager\Feature;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
interface BlockProviderInterface
{
    /**
     * retrieve block config
     *
     * @return array
     */
    public function getBlockConfig();
}
