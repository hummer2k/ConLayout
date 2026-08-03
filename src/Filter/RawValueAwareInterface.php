<?php

declare(strict_types=1);

namespace ConLayout\Filter;

interface RawValueAwareInterface
{
    /**
     * @var mixed $value raw value
     */
    public function setRawValue(mixed $value): void;
}
