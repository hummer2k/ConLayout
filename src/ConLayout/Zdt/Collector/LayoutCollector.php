<?php
namespace ConLayout\Zdt\Collector;

use ConLayout\Layout\LayoutInterface;
use ConLayout\Listener\LayoutUpdateListener;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\Mvc\MvcEvent;
use ZendDeveloperTools\Collector\AbstractCollector;

/**
 * Collector for ZendDeveloperToolbar
 *
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutCollector extends AbstractCollector
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
     * @param MvcEvent $mvcEvent
     * @return LayoutCollector
     */
    public function collect(MvcEvent $mvcEvent)
    {
        $layout = $mvcEvent->getViewModel();
        $blocks = [];
        foreach ($this->layout->getBlocks() as $blockName => $block) {
            $blocks[$blockName] = [
                'template' => $block->getTemplate(),
                'capture_to' => $block->captureTo(),
                'class' => get_class($block)
            ];
        }
        $data = [
            'handles' => $this->updater->getHandles(true),
            'layout_structure' => $this->updater->getLayoutStructure()->toArray(),
            'blocks' => $blocks,
            'layout_template' => $layout->getTemplate(),
            'current_area' => $this->updater->getArea()
        ];

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
     * @return string
     */
    public function getCurrentArea()
    {
        return $this->data['current_area'];
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
