<?php

namespace ConLayout\Priority;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ControllerAction
{
    /**
     *
     * @param string $handle
     * @param string $substr
     * @return int
     */
    public static function getPriority($handle, $substr)
    {
        return (substr_count($handle, '\\') + 5);
    }
}