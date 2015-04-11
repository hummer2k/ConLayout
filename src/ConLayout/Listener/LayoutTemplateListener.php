<?php

namespace ConLayout\Listener;

use ConLayout\Updater\LayoutUpdaterInterface;
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
     * @var LayoutUpdaterInterface
     */
    protected $updater;

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
            $layoutTemplate = $this->updater->getLayoutStructure()->get(
                LayoutUpdaterInterface::INSTRUCTION_LAYOUT_TEMPLATE, ''
            );
            $layout->setTemplate($layoutTemplate);
        }
    }
}
