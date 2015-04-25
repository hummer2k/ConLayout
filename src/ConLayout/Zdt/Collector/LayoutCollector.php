<?php
namespace ConLayout\Zdt\Collector;

use ConLayout\Layout\LayoutInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
use ZendDeveloperTools\Collector\AbstractCollector;
use Zend\Mvc\MvcEvent;

/**
 * Collector for ZendDeveloperToolbar
 *
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutCollector
    extends AbstractCollector
{
    const NAME = 'con-layout';

    /**
     *
     * @var LayoutInterface
     */
    protected $layout;

    /**
     *
     * @var LayoutUpdaterInterface
     */
    protected $updater;

    /**
     *
     * @param LayoutInterface $layout
     * @param LayoutUpdaterInterface $updater
     */
    public function __construct(LayoutInterface $layout, LayoutUpdaterInterface $updater)
    {
        $this->layout  = $layout;
        $this->updater = $updater;
    }

        /**
     * 
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
    
    /**
     * 
     * @return int
     */
    public function getPriority()
    {
        return 600;
    }

    /**
     * collect data for zdt
     * 
     * @param \Zend\Mvc\MvcEvent $mvcEvent
     * @return LayoutCollector
     */
    public function collect(MvcEvent $mvcEvent)
    {
        $layout = $mvcEvent->getViewModel();
        $data = array(
            'handles' => $this->updater->getHandles(true),
            'layout_structure' => $this->updater->getLayoutStructure()->toArray(),
            'blocks' => $this->layout->getBlocks(),
            'layout_template' => $layout->getTemplate()
        );

        $this->data = $data;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getLayoutTemplate()
    {
        return $this->data['layout_template'];
    }
    
    /**
     * 
     * @return array
     */
    public function getHandles()
    {
        return $this->data['handles'];
    }
    
    /**
     * 
     * @return array
     */
    public function getLayoutStructure()
    {
        return $this->data['layout_structure'];
    }
    
    /**
     * 
     * @return array
     */
    public function getBlocks()
    {
        return $this->data['blocks'];
    }
}
