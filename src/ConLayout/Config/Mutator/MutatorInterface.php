<?php

namespace ConLayout\Config\Mutator;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
interface MutatorInterface
{
    /**
     * @param array $config
     * @return array
     */
    public function mutate(array $config);
}