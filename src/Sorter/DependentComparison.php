<?php
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Sorter;

final class DependentComparison implements SorterInterface
{
    /**
     * @inheritDoc
     */
    public function sort(array $data)
    {
        $tmp = [];
        foreach (array_keys($data) as $key) {
            $tmp[] = $this->getNodeLevel($data, $key);
        }
        array_multisort($tmp, SORT_ASC, $data);
        return $data;
    }

    /**
     *
     * @param array $array
     * @param string $key
     * @param array $references
     * @return int
     */
    private function getNodeLevel(array $array, $key, array $references = [])
    {
        if (!isset($array[$key]['depends'])) {
            return 0;
        }
        if (in_array($key, $references)) {
            return -1;
        }
        $references[] = $key;
        $level = $this->getNodeLevel($array, $array[$key]['depends'], $references);
        return ($level == -1 ? -1 : $level + 1);
    }
}
