<?php

declare(strict_types=1);

namespace ConLayout\Sorter;

final class DependentComparison implements SorterInterface
{
    /**
     * @inheritDoc
     */
    public function sort(array $data): array
    {
        $tmp = [];
        foreach (array_keys($data) as $key) {
            $tmp[] = $this->getNodeLevel($data, $key);
        }
        array_multisort($tmp, SORT_ASC, $data);
        return $data;
    }

    private function getNodeLevel(array $array, string $key, array $references = []): int
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
