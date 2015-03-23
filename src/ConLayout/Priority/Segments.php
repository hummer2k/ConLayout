<?php

namespace ConLayout\Priority;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Segments
{
    /**
     *
     * @param string $handle
     * @param string $substr
     * @return int
     */
    public static function getPriority($handle, $substr)
    {
        return substr_count($handle, $substr);
    }
}
