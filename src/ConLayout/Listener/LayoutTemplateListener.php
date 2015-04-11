<?php

namespace ConLayout\Listener;

use ConLayout\LayoutInterface;
use Zend\View\Model\ModelInterface;

/**
 * Listener to set the layout template
 *
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutTemplateListener
{
    /**
     *
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @param LayoutInterface $layout
     */
    public function __construct(Layout $layout)
    {
        $this->layout = $layout;
    }

    /**
     * set layout template if no template was set e.g. through controller
     * layout plugin
     *
     * @param MvcEvent $e
     */
    public function setLayoutTemplate(MvcEvent $e)
    {
        /* @var $layout ModelInterface */
        $layout = $e->getViewModel();
        $template = $layout->getTemplate();
        if ($template === '') {
            $layout->setTemplate($this->layout->getLayoutTemplate());
        }
    }
}
