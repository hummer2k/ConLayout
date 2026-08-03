<?php

declare(strict_types=1);

namespace ConLayout\Sorter;

interface SorterInterface
{
    public function sort(array $data): array;
}
