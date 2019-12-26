<?php

namespace ConLayout\View\Helper;

use Laminas\View\Helper\AbstractHtmlElement;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Wrapper extends AbstractHtmlElement
{
    public const DEFAULT_TAG = 'div';

    /**
     *
     * @var string
     */
    protected $tag;

    /**
     *
     * @param string $tag
     * @return Wrapper
     */
    public function __invoke($tag = null)
    {
        $this->tag = $tag ?: static::DEFAULT_TAG;
        return $this;
    }

    /**
     *
     * @param mixed $attributes
     * @return string
     */
    public function openTag($attributes = [])
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

    /**
     *
     * @return string
     */
    public function closeTag()
    {
        return '</' . $this->tag . '>';
    }
}
