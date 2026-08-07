<?php

declare(strict_types=1);

namespace ConLayout\Filter;

use Laminas\Filter\FilterInterface;

/**
 * @template-implements FilterInterface<string>
 */
class CacheBusterFilter implements FilterInterface, RawValueAwareInterface
{
    protected ?string $rawValue = null;

    public function __construct(private readonly string $internalBasePath = './public') {}

    public function filter(mixed $value): string
    {
        $file = $this->getFilePath($value);
        if (is_file($file) && is_readable($file)) {
            return $value . '?v=' . substr((string) md5_file($file), 0, 8);
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

    protected function getFilePath(mixed $value): string
    {
        if (null !== $this->rawValue) {
            $value = $this->rawValue;
        }
        return $this->internalBasePath . '/' . ltrim((string) $value, '/');
    }
}
