<?php

namespace ConLayout\Layout;

use ConLayout\Block\BlockPoolInterface;
use ConLayout\Generator\GeneratorInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\Stdlib\PriorityList;
use Laminas\View\Model\ModelInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Layout implements
    EventManagerAwareInterface,
    LayoutInterface
{
    use EventManagerAwareTrait;

    /**
     * flag if blocks have already been injected
     *
     * @var bool
     */
    protected $blocksInjected = false;

    /**
     *
     * @var LayoutUpdaterInterface
     */
    protected $updater;

    /**
     * blocks registry
     *
     * @var BlockPoolInterface
     */
    protected $blockPool;

    /**
     *
     * @var GeneratorInterface[]|PriorityList
     */
    protected $generators;

    /**
     * @var array
     */
    protected $loadedGenerators = [];

    /**
     * flag if layout has been loaded
     *
     * @var bool
     */
    protected $isLoaded = false;

    /**
     *
     * @param LayoutUpdaterInterface $updater
     * @param BlockPoolInterface $blockPool
     */
    public function __construct(
        LayoutUpdaterInterface $updater,
        BlockPoolInterface $blockPool
    ) {
        $this->updater    = $updater;
        $this->blockPool  = $blockPool;
        $this->generators = new PriorityList();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoot()
    {
        return $this->getBlock(self::BLOCK_ID_ROOT);
    }

    /**
     * @inheritDoc
     */
    public function setRoot(ModelInterface $root)
    {
        $this->blockPool->add(self::BLOCK_ID_ROOT, $root);
        return $this;
    }

    /**
     * inject blocks into the root view model
     *
     * @return LayoutInterface
     */
    public function load()
    {
        if (false === $this->isLoaded) {
            $this->getEventManager()->trigger(
                __FUNCTION__ . '.pre',
                $this,
                []
            );

            $this->generate();
            $this->injectBlocks();
            $this->isLoaded = true;

            $this->getEventManager()->trigger(
                __FUNCTION__ . '.post',
                $this,
                []
            );
        }
        return $this;
    }

    /**
     * @param array $generators
     * @return mixed|void
     */
    public function generate(array $generators = [])
    {
        $layoutStructure = $this->updater->getLayoutStructure();
        foreach ($this->generators as $name => $generator) {
            if (
                !$this->isGeneratorLoaded($name) &&
                (
                    empty($generators) ||
                    isset($generators[$name])
                )
            ) {
                $generator->generate($layoutStructure);
                $this->loadedGenerators[$name] = true;
            }
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    private function isGeneratorLoaded($name)
    {
        return isset($this->loadedGenerators[$name]);
    }

    /**
     * @inheritDoc
     */
    public function injectBlocks()
    {
        if (false === $this->blocksInjected) {
            $this->blockPool->sort();
            $blocks = $this->blockPool->get();
            foreach ($blocks as $blockId => $block) {
                if (
                    $this->isAllowed($blockId, $block) &&
                    $blockId !== self::BLOCK_ID_ROOT
                ) {
                    list($parent, $captureTo) = $this->getCaptureTo($block);
                    if ($parentBlock = $this->getBlock($parent)) {
                        $parentBlock->addChild($block, $captureTo);
                        $block->setOption('parent_block', $parentBlock);
                    }
                }
            }
            $this->blocksInjected = true;
        }
    }

    /**
     * Determines whether a block should be allowed given certain parameters
     *
     * @param   string          $blockId
     * @param   ModelInterface  $block
     * @return  bool
     */
    private function isAllowed($blockId, ModelInterface $block)
    {
        $result = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'block_id' => $blockId,
                'block' => $block
            ]
        );
        if ($result->stopped()) {
            return $result->last();
        }
        return true;
    }

    /**
     * adds a block to the registry
     *
     * @param   string          $blockId
     * @param   ModelInterface  $block
     * @return  LayoutInterface
     */
    public function addBlock($blockId, ModelInterface $block)
    {
        $this->blockPool->add($blockId, $block);
        return $this;
    }

    /**
     * removes a single block from block registry
     *
     * @param   string          $blockId
     * @return  LayoutInterface
     */
    public function removeBlock($blockId)
    {
        $this->blockPool->remove($blockId);
        return $this;
    }

    /**
     * retrieve parent and capture_to as array, e.g.: [ 'layout', 'content' ]
     * so we are able to list() block_id and capture_to values
     *
     * @param   ModelInterface  $block
     * @return  array
     */
    protected function getCaptureTo(ModelInterface $block)
    {
        $captureTo = $block->captureTo();
        if ($parent = $block->getOption('parent')) {
            $captureTo = explode(self::CAPTURE_TO_DELIMITER, $captureTo);
            return [
                $parent,
                end($captureTo)
            ];
        }
        if (false !== strpos($captureTo, self::CAPTURE_TO_DELIMITER)) {
            return explode(self::CAPTURE_TO_DELIMITER, $captureTo);
        }
        return [
            self::BLOCK_ID_ROOT,
            $captureTo
        ];
    }

    /**
     * retrieve single block by its id
     *
     * @param   string                  $blockId
     * @return  false|ModelInterface
     */
    public function getBlock($blockId)
    {
        return $this->blockPool->get($blockId);
    }

    /**
     * retrieve generated blocks
     * format:
     * [
     *     'block_id' => ModelInterface
     * ]
     *
     * @return ModelInterface[]
     */
    public function getBlocks()
    {
        return $this->blockPool->get();
    }

    /**
     * @inheritDoc
     */
    public function attachGenerator($name, GeneratorInterface $generator, $priority = 1)
    {
        $this->generators->insert($name, $generator, $priority);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function detachGenerator($name)
    {
        $this->generators->remove($name);
        return $this;
    }
}
