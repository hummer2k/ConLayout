<?php

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Block;

class Container extends AbstractBlock
{
    /**
     * @return mixed
     */
    protected function getTag()
    {
        return $this->getOption('tag', 'div');
    }

    /**
     * @return string
     */
    public function openTag()
    {
        return '<' . $this->getTag() . $this->htmlAttributes() . '>';
    }

    /**
     * @return string
     */
    public function closeTag()
    {
        return '</' . $this->getTag() . '>';
    }

    /**
     * @return string
     */
    protected function htmlAttributes()
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
