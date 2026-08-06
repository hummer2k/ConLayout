<?php

declare(strict_types=1);

namespace ConLayout\Filter;

use Laminas\Filter\FilterInterface;

class CacheBusterFilter implements FilterInterface, RawValueAwareInterface
{
    protected ?string $rawValue = null;

    public function __construct(private readonly string $internalBasePath = './public') {}

    /**
     *
     * @param string $value
     */
    public function filter($value): string
    {
        $file = $this->getFilePath($value);
        if (is_file($file) && is_readable($file)) {
            return $value . '?v=' . substr(md5_file($file), 0, 8);
        }
        return $value;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }

    public function setRawValue(mixed $value): void
    {
        $this->rawValue = (string) $value;
    }

    /**
     *
     * @param string $value
     */
    protected function getFilePath($value): string
    {
        if (null !== $this->rawValue) {
            $value = $this->rawValue;
        }
        return $this->internalBasePath . '/' . ltrim($value, '/');
    }
}
