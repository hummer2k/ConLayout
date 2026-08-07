<?php

declare(strict_types=1);

namespace ConLayout\View\Helper;

use Laminas\View\Helper\HtmlAttributes;

class Wrapper
{
    public const string DEFAULT_TAG = 'div';

    protected string $tag = self::DEFAULT_TAG;

    public function __construct(private readonly HtmlAttributes $htmlAttributes) {}

    public function __invoke(?string $tag = null): static
    {
        $this->tag = null !== $tag ? $tag : static::DEFAULT_TAG;
        return $this;
    }

    /**
     *
     * @param array<string, array<array-key, mixed>|null|scalar> $attributes
     */
    public function openTag(array $attributes = []): string
    {
        $htmlAttribs = '';
        if (count($attributes)) {
            $htmlAttribs = (string) ($this->htmlAttributes)($attributes);
        }
        return sprintf(
            '<%s%s>',
            $this->tag,
            $htmlAttribs,
        );
    }

    public function closeTag(): string
    {
        return '</' . $this->tag . '>';
    }
}
