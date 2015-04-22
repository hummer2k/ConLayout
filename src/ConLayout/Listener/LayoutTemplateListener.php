<?php

namespace ConLayout\Listener;

use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\View\Model\ModelInterface;
use Zend\Mvc\MvcEvent;

/**
 * Listener to set the layout template
 *
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutTemplateListener
    implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     *
     * @var LayoutUpdaterInterface
     */
    protected $updater;

    /**
     *
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'setLayoutTemplate'));
    }

    /**
     * 
     * @param LayoutUpdaterInterface $updater
     */
    public function __construct(LayoutUpdaterInterface $updater)
    {
        $this->updater = $updater;
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
            $layoutTemplate = $this->updater->getLayoutStructure()->get(
                LayoutUpdaterInterface::INSTRUCTION_LAYOUT_TEMPLATE, ''
            );
            $layout->setTemplate($layoutTemplate);
        }
    }
}
