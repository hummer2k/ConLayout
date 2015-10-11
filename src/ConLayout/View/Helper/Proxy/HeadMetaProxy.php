<?php

namespace ConLayout\View\Helper\Proxy;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class HeadMetaProxy extends AbstractViewHelperProxy
{

    public function appendName($name, $content, $modifiers = array())
    {
        return $this->helper->appendName($name, $content, $modifiers);
    }

    public function prependName($name, $content, $modifiers = array())
    {
        return $this->helper->prependName($name, $content, $modifiers);
    }

    public function setName($name, $content, $modifiers = array())
    {
        return $this->helper->setName($name, $content, $modifiers);
    }

    public function appendHttpEquiv($keyValue, $content, $modifiers = array())
    {
        return $this->helper->appendHttpEquiv($keyValue, $content, $modifiers);
    }

    public function prependHttpEquiv($keyValue, $content, $modifiers = array())
    {
        return $this->helper->prependHttpEquiv($keyValue, $content, $modifiers);
    }

    public function setHttpEquiv($keyValue, $content, $modifiers = array())
    {
        return $this->helper->setHttpEquiv($keyValue, $content, $modifiers);
    }

    public function appendProperty($property, $content, $modifiers = array())
    {
        return $this->helper->appendProperty($property, $content, $modifiers);
    }

    public function prependProperty($property, $content, $modifiers = array())
    {
        return $this->helper->prependProperty($property, $content, $modifiers);
    }

    public function setProperty($property, $content, $modifiers = array())
    {
        return $this->helper->setProperty($property, $content, $modifiers);
    }
}
