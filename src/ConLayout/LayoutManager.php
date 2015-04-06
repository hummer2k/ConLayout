<?php

namespace ConLayout;

use ConLayout\Factory\BlockFactoryInterface;
use ConLayout\Handle\Handle;
use ConLayout\Handle\HandleInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Model\ModelInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutManager implements
    EventManagerAwareInterface,
    LayoutManagerInterface
{
    use EventManagerAwareTrait;

    const NAME_LAYOUT = 'layout';
    const CAPTURE_TO_DELIMITER = '::';

    /**
     *
     * @var HandleInterface[]
     */
    protected $handles = [];

    /**
     * blocks registry
     *
     * @var ModelInterface[]
     */
    protected $blocks = [];

    /**
     *
     * @var BlockFactoryInterface
     */
    protected $blockFactory;

    /**
     *
     * @param BlockFactoryInterface $blockFactory
     */
    public function __construct(BlockFactoryInterface $blockFactory)
    {
        $this->blockFactory = $blockFactory;
        $this->handles = [
            new Handle('default', -1)
        ];
    }

    /**
     *
     * @return LayoutManager
     */
    public function loadLayout()
    {
        $this->generateBlocks();
        $this->sortBlocks();
        return $this;
    }

    /**
     *
     * @param array $blockConfig
     * @return array
     */
    public function generateBlocks()
    {
        foreach ($blockConfig as $blockId => $specs) {
            $this->addBlock($blockId, $this->blockFactory->createBlock($blockId, $specs));
        }
        return $this;
    }

    /**
     * 
     * @return LayoutManagerInterface
     */
    public function sortBlocks()
    {
        uasort($this->blocks, function($a, $b) {
            /* @var $a ModelInterface */
            /* @var $b ModelInterface */
            $orderA = $a->getOption('order', 0);
            $orderB = $b->getOption('order', 0);
            if ($orderA == $orderB) {
                return 0;
            }
            return ($orderA < $orderB) ? -1 : 1;
        });
        return $this;
    }

    /**
     *
     * @param ModelInterface $layout
     * @return LayoutManagerInterface
     */
    public function injectBlocks(ModelInterface $layout)
    {
        $this->addBlock(self::NAME_LAYOUT, $layout);
        foreach ($this->getBlocks() as $blockId => $block) {
            if (!$this->isAllowed($blockId, $block)) continue;
            list($parent, $captureTo) = $this->getCaptureTo($block);
            if ($parentBlock = $this->getBlock($parent)) {
                $parentBlock->addChild($block, $captureTo);
            }
        }
        return $this;
    }

    /**
     * Determines whether a block should be allowed given certain parameters
     *
     * @param   string          $blockId
     * @param   ModelInterface  $block
     * @return  bool
     */
    protected function isAllowed($blockId, ModelInterface $block)
    {
        if ($blockId === self::NAME_LAYOUT) {
            return false;
        }
        $results = $this->getEventManager()->trigger(__FUNCTION__, $this, ['block' => $block, 'block_id' => $blockId]);
        $isAllowed = $results->last();
        return $isAllowed;
    }

    /**
     *
     * @param string $blockId
     * @param ModelInterface $block
     * @return LayoutManagerInterface
     */
    public function addBlock($blockId, ModelInterface $block)
    {
        $this->blocks[$blockId] = $block;
        return $this;
    }

    /**
     *
     * @param string $blockId
     * @return LayoutManagerInterface
     */
    public function removeBlock($blockId)
    {
        if (isset($this->blocks[$blockId])) {
            unset($this->blocks[$blockId]);
        }
        return $this;
    }

    /**
     * retrieve parent and capture_to as array, e.g.: [ 'layout', 'content' ]
     *
     * @param ModelInterface $block
     * @return array
     */
    protected function getCaptureTo(ModelInterface $block)
    {
        $captureTo = $block->captureTo();
        if (false !== strpos($captureTo, self::CAPTURE_TO_DELIMITER)) {
            return explode(self::CAPTURE_TO_DELIMITER, $captureTo);
        }
        return [
            self::NAME_LAYOUT,
            $captureTo
        ];
    }

    /**
     *
     * @param string $blockId
     * @return false|ModelInterface
     */
    public function getBlock($blockId)
    {
        return isset($this->blocks[$blockId])
            ? $this->blocks[$blockId]
            : false;
    }

    /**
     * @return ModelInterface[]
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     *
     * @param HandleInterface $handle
     * @return LayoutManager
     */
    public function addHandle(Handle\HandleInterface $handle)
    {
        $this->handles[] = $handle;
        return $this;
    }

    /**
     *
     * @return HandleInterface[]
     */
    public function getHandles()
    {
        return $this->handles;
    }

    /**
     *
     * @param string $handle
     * @return LayoutManager
     */
    public function removeHandle($handle)
    {
        foreach ($this->handles as $key => $handle) {
            if ($handle->getName() === $handle) {
                unset($this->handles[$key]);
            }
        }
        return $this;
    }

    /**
     *
     * @param EventManagerInterface $eventManager
     * @return LayoutManagerInterface
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
}
