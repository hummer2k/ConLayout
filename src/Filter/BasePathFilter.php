<?php

declare(strict_types=1);

namespace ConLayout\Filter;

use Laminas\Filter\FilterInterface;
use Laminas\View\Helper\BasePath as BasePathHelper;

/**
 * @template-implements FilterInterface<string>
 */
readonly class BasePathFilter implements FilterInterface
{
    public function __construct(private BasePathHelper $basePathHelper) {}

    /**
     * @param mixed $value asset url to prepare
     * @return string prepared asset url
     */
    public function filter(mixed $value): string
    {
        $value = (string) $value;
        $urlHost = parse_url($value, PHP_URL_HOST);
        if (null === $urlHost || false === $urlHost) {
            return ($this->basePathHelper)($value);
        }
        return $value;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
