<?php

declare(strict_types=1);

namespace ConLayout\Generator;

use Laminas\Config\Config;

interface GeneratorInterface
{
    public function generate(Config $layoutStructure): void;
}
