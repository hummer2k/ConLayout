<?php
namespace ConLayout\Zdt\Collector;

use ConLayout\Layout\LayoutInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\Resolver\ResolverInterface;
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
     * @var ResolverInterface
     */
    protected $viewResolver;

    /**
     *
     * @param LayoutInterface $layout
     * @param LayoutUpdaterInterface $updater
     */
    public function __construct(
        LayoutInterface $layout,
        LayoutUpdaterInterface $updater,
        ResolverInterface $viewResolver
    ) {
        $this->layout  = $layout;
        $this->updater = $updater;
        $this->viewResolver = $viewResolver;
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
        foreach ($this->layout->getBlocks() as $blockId => $block) {
            if ($parentBlock = $block->getOption('parent_block')) {
                $captureTo = $parentBlock->getVariable(LayoutInterface::BLOCK_ID_VAR) . '::' . $block->captureTo();
            } else {
                $captureTo = $block->captureTo();
            }
            $blocks[$blockId] = [
                'instance' => $block,
                'template' => $this->resolveTemplate($block->getTemplate()),
                'capture_to' => $captureTo,
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
     * retrieve resolved template path
     *
     * @param string $template
     * @return string
     */
    private function resolveTemplate($template)
    {
        $template = str_replace(
            getcwd(),
            '',
            $this->viewResolver->resolve($template)
        );
        return $template;
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
