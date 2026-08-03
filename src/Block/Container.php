<?php

declare(strict_types=1);

namespace ConLayout\Block;

class Container extends AbstractBlock
{
    protected function getTag(): string
    {
        return $this->getOption('tag', 'div');
    }

    public function openTag(): string
    {
        return '<' . $this->getTag() . $this->htmlAttributes() . '>';
    }

    public function closeTag(): string
    {
        return '</' . $this->getTag() . '>';
    }

    protected function htmlAttributes(): string
    {
        $html = '';
        foreach ($this->getOptions() as $name => $value) {
            if (0 === strpos($name, 'html_')) {
                $value = $this->getView()->escapeHtmlAttr($value);
                $html .= sprintf(' %s="%s"', substr($name, 5), $value);
            }
        }
        return $html;
    }
}
