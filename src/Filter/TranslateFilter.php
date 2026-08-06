<?php

declare(strict_types=1);

namespace ConLayout\Filter;

use Laminas\Filter\FilterInterface;
use Laminas\I18n\Translator\TranslatorInterface;

readonly class TranslateFilter implements FilterInterface
{
    public function __construct(private TranslatorInterface $translator) {}

    public function filter($value): string
    {
        return $this->translator->translate((string) $value);
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
