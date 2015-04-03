<?php

namespace ConLayout\Config\Mutator;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
abstract class AbstractMutator implements MutatorInterface
{
    /**
     * determine if key is directive or block name
     *
     * @param string $directiveOrCaptureTo
     * @return boolean
     */
    protected function isDirective($directiveOrCaptureTo)
    {
        return substr($directiveOrCaptureTo, 0, 1) === '_';
    }
}
