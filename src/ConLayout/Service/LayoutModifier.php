<?php
namespace ConLayout\Service;

use ConLayout\Debugger;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Model\ViewModel;

/**
 * Modifier
 *
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutModifier implements EventManagerAwareInterface
{
    /**
     *
     * @var ViewModel
     */
    protected $layout;
        
    /**
     *
     * @var default captureTo
     */
    protected $captureTo = 'childHtml';
    
    /**
     *
     * @var boolean
     */
    protected $isDebug = false;

    /**
     *
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     *
     * @var Debugger
     */
    protected $debugger;

    /**
     *
     * @param Debugger $debugger
     */
    public function __construct(Debugger $debugger)
    {
        $this->debugger = $debugger;
    }

    /**
     * 
     * @param array $blocks
     * @param mixed $parent
     * @return \ConLayout\Service\Layout\Modifier
     */
    public function addBlocksToLayout(array $blocks, $parent = null)
    {
        foreach ($blocks as $captureTo => $blocks) {
            $this->sortBlocks($blocks);
            foreach ($blocks as $block) {
                if (!$this->isAllowed($block)) continue;
                $captureTo = !is_string($captureTo) ? $this->captureTo : $captureTo;
                $blockInstance = $block['instance'];
                if ($this->debugger->isEnabled()) {
                    $block['instance'] = $this->debugger->addDebugBlock($block['instance'], $captureTo);
                }
                $append = (isset($block['append']) && false === $block['append']) ? false : true;
                $parent->addChild($block['instance'], $captureTo, $append);
                if (isset($block['children'])) {
                    $this->addBlocksToLayout($block['children'], $blockInstance);
                }
            }
        }
        return $this;
    }

    /**
     *
     * @param array $blocks
     * @return array
     */
    protected function sortBlocks(array &$blocks)
    {
        uasort($blocks, function($a, $b) {
            $orderA = $a['instance']->getOption('order', 0);
            $orderB = $b['instance']->getOption('order', 0);
            if ($orderA == $orderB) {
                return 0;
            }
            return ($orderA < $orderB) ? -1 : 1;
        });
    }
    
    /**
     * Determines whether a block should be allowed given certain parameters
     *
     * @param   array   $block
     * @return  bool
     */
    protected function isAllowed(array $block)
    {
        $results = $this->getEventManager()->trigger(__FUNCTION__, $this, ['block' => $block]);
        $isAllowed = $results->last();
        return $isAllowed;
    }
        
    /**
     * 
     * @param string $captureTo
     */
    public function setCaptureTo($captureTo)
    {
        $this->captureTo = (string) $captureTo;
        return $this;
    }

    /**
     *
     * @param EventManagerInterface $eventManager
     * @return LayoutModifier
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->setIdentifiers([__CLASS__]);
        $this->eventManager = $eventManager;

        $this->eventManager->getSharedManager()->attach(__CLASS__, 'isAllowed', function() {
            return true;
        }, 10000);

        return $this;
    }

    /**
     * retrieve event manager
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }
}
