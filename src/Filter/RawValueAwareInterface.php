<?php

declare(strict_types=1);

namespace ConLayout\Filter;

interface RawValueAwareInterface
{
    /**
     * @param mixed $value raw value
     */
    public function setRawValue(mixed $value): void;
}
