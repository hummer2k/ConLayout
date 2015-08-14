<?php

namespace ConLayout\View\Helper;

use Zend\View\Helper\AbstractHtmlElement;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Wrapper extends AbstractHtmlElement
{
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
        $this->tag = $tag ?: 'div';
        return $this;
    }

    /**
     *
     * @param mixed $attributes
     * @return string
     */
    public function openTag($attributes = [])
    {
        return '<' . $this->tag . ' ' . $this->htmlAttribs($attributes) . '>';
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
