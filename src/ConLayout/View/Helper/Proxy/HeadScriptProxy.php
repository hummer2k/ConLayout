<?php

namespace ConLayout\View\Helper\Proxy;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class HeadScriptProxy extends AbstractViewHelperProxy
{

    public function appendFile($src, $type = 'text/javascript', $attrs = array())
    {
        return $this->helper->appendFile($src, $type, $attrs);
    }

    public function offsetSetFile($index, $src, $type = 'text/javascript', $attrs = array())
    {
        return $this->helper->offsetSetFile($index, $src, $type, $attrs);
    }

    public function prependFile($src, $type = 'text/javascript', $attrs = array())
    {
        return $this->helper->prependFile($src, $type, $attrs);
    }

    public function setFile($src, $type = 'text/javascript', $attrs = array())
    {
        return $this->helper->setFile($src, $type, $attrs);
    }

    public function appendScript($script, $type = 'text/javascript', $attrs = array())
    {
        return $this->helper->appendScript($script, $type, $attrs);
    }

    public function offsetSetScript($index, $src, $type = 'text/javascript', $attrs = array())
    {
        return $this->helper->offsetSetScript($index, $src, $type, $attrs);
    }

    public function prependScript($script, $type = 'text/javascript', $attrs = array())
    {
        return $this->helper->prependScript($script, $type, $attrs);
    }

    public function setScript($script, $type = 'text/javascript', $attrs = array())
    {
        return $this->helper->setScript($script, $type, $attrs);
    }
}
