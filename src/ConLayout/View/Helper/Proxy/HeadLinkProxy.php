<?php

namespace ConLayout\View\Helper\Proxy;

use ConLayout\View\Helper\Proxy\AbstractViewHelperProxy;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class HeadLinkProxy extends AbstractViewHelperProxy
{
    public function appendStylesheet($href, $media = 'screen', $conditionalStylesheet = '', $extras = [])
    {
        return $this->helper->appendStylesheet($href, $media, $conditionalStylesheet, $extras);
    }

    public function offsetSetStylesheet($index, $href, $media = 'screen', $conditionalStylesheet = '', $extras = [])
    {
        return $this->helper->offsetSetStylesheet($index, $href, $media, $conditionalStylesheet, $extras);
    }

    public function prependStylesheet($href, $media = 'screen', $conditionalStylesheet = '', $extras = [])
    {
        return $this->helper->prependStylesheet($href, $media, $conditionalStylesheet, $extras);
    }

    public function setStylesheet($href, $media = 'screen', $conditionalStylesheet = '', $extras = [])
    {
        return $this->helper->setStylesheet($href, $media, $conditionalStylesheet, $extras);
    }

    public function appendAlternate($href, $type, $title, $extras = [])
    {
        return $this->helper->appendAlternate($href, $type, $title, $extras);
    }

    public function offsetSetAlternate($index, $href, $type, $title, $extras = [])
    {
        return $this->helper->offsetSetAlternate($index, $href, $type, $title, $extras);
    }

    public function prependAlternate($href, $type, $title, $extras = [])
    {
        return $this->helper->prependAlternate($href, $type, $title, $extras);
    }

    public function setAlternate($href, $type, $title, $extras = [])
    {
        return $this->helper->setAlternate($href, $type, $title, $extras);
    }
}
