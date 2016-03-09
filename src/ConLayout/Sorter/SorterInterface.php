<?php
/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Sorter;

interface SorterInterface
{
    /**
     * @param $data
     * @return mixed
     */
    public function sort($data);
}
