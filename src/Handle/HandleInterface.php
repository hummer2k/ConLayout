<?php

declare(strict_types=1);

namespace ConLayout\Handle;

interface HandleInterface
{
    public function getName(): string;

    public function getPriority(): int;

    public function __toString(): string;
}
