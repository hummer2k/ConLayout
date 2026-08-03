<?php

declare(strict_types=1);

namespace ConLayout\Updater\Collector;

use Laminas\Config\Config;

interface CollectorInterface
{
    public function collect(string $handle, ?string $area = null): Config;
}
