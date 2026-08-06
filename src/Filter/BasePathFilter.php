<?php

declare(strict_types=1);

namespace ConLayout\Filter;

use Laminas\Filter\FilterInterface;
use Laminas\View\Helper\BasePath as BasePathHelper;

readonly class BasePathFilter implements FilterInterface
{
    public function __construct(private BasePathHelper $basePathHelper) {}

    /**
     *
     * @param string $value asset url to prepare
     * @return string prepared asset url
     */
    public function filter($value): string
    {
        $urlHost = parse_url($value, PHP_URL_HOST);
        if (empty($urlHost)) {
            return ($this->basePathHelper)($value);
        }
        return $value;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
