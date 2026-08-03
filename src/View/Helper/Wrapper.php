<?php

declare(strict_types=1);

namespace ConLayout\View\Helper;

use Laminas\View\Helper\AbstractHtmlElement;

class Wrapper extends AbstractHtmlElement
{
    public const DEFAULT_TAG = 'div';

    protected string $tag = self::DEFAULT_TAG;

    public function __invoke(?string $tag = null): static
    {
        $this->tag = $tag ?: static::DEFAULT_TAG;
        return $this;
    }

    /**
     *
     * @param mixed $attributes
     */
    public function openTag(array $attributes = []): string
    {
        $htmlAttribs = '';
        if (count($attributes)) {
            $htmlAttribs = $this->htmlAttribs($attributes);
        }
        return sprintf(
            '<%s%s>',
            $this->tag,
            $htmlAttribs
        );
    }

    public function closeTag(): string
    {
        return '</' . $this->tag . '>';
    }
}
