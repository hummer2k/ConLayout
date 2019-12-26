<?php

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Sorter;

class OrderComparison implements SorterInterface
{
    /**
     * @inheritDoc
     */
    public function sort(array $data)
    {
        uasort($data, function ($a, $b) {
            $orderA = isset($a['order']) ? $a['order'] : 1;
            $orderB = isset($b['order']) ? $b['order'] : 1;
            if ($orderA == $orderB) {
                return 0;
            }
            return $orderA > $orderB ? 1 : -1;
        });
        return $data;
    }
}
