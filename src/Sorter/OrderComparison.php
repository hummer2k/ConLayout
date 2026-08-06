<?php

declare(strict_types=1);

namespace ConLayout\Sorter;

class OrderComparison implements SorterInterface
{
    /**
     * @inheritDoc
     */
    public function sort(array $data): array
    {
        uasort($data, fn($a, $b) => ($a['order'] ?? 1) <=> ($b['order'] ?? 1));
        return $data;
    }
}
