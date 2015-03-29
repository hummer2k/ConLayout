<?php

namespace ConLayout\Config\Modifier;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
interface ModifierInterface
{
    /**
     * @param array $config
     * @return array
     */
    public function modify(array $config);
}