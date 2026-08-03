<?php

declare(strict_types=1);

namespace ConLayout\ModuleManager\Feature;

interface BlockProviderInterface
{
    /**
     * retrieve block config
     */
    public function getBlockConfig(): array;
}
