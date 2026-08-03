<?php

declare(strict_types=1);

namespace ConLayout\Filter;

use Laminas\Filter\FilterInterface;
use Laminas\I18n\Translator\TranslatorInterface;

class TranslateFilter implements FilterInterface
{
    protected \Laminas\I18n\Translator\TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function filter($value): string
    {
        return $this->translator->translate((string) $value);
    }
}
