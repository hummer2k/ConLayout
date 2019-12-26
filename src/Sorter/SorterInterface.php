<?php

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Sorter;

interface SorterInterface
{
    /**
     * @param array $data
     * @return mixed
     */
    public function sort(array $data);
}
