<?php
namespace ConLayout\Listener;

use ConLayout\Layout\LayoutInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ModelInterface;
use Zend\View\Model\ViewModel;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class InjectBlocksListener
    implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;
            
    /**
     *
     * @var LayoutInterface
     */
    protected $layout;
    
    /**
     *
     * @var int
     */
    protected static $anonymousSuffix = 1;

    /**
     * 
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'injectBlocks'));
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'setLayoutTemplate'));
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'applyHelpers'));
    }
    
    /**
     * inject blocks to root ViewModel or layout if not flagged as terminal
     * 
     * @param MvcEvent $e
     */
    public function injectBlocks(MvcEvent $e)
    {
        /* @var $root ModelInterface */
        $root = $e->getViewModel();
        if ($root->terminate()) {
            return;
        }
        foreach ($root->getChildren() as $childModel) {
            $blockId = $this->determineAnonymousBlockId($childModel);
            $childModel->setVariable(
                'nameInLayout',
                $blockId
            );
            $childModel->setVariable('blockType', 'custom');
            $this->layout->addBlock($blockId, $childModel);
        }
        $root->clearChildren();
        $this->layout->injectBlocks($root);
    }

    /**
     *
     * @param ViewModel $viewModel
     * @return string
     */
    private function determineAnonymousBlockId(ViewModel $viewModel)
    {
        $blockName = $viewModel->getVariable(
            'nameInLayout',
            sprintf(
                'anonymous.%s.%s',
                $viewModel->captureTo(),
                self::$anonymousSuffix++
            )
        );
        return $blockName;
    }
}
