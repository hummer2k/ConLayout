<?php
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Generator;

use ConLayout\Block\BlockPoolInterface;
use ConLayout\Block\Factory\BlockFactoryInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\Config\Config;

final class BlocksGenerator implements GeneratorInterface
{
    const NAME                  = 'blocks';
    const INSTRUCTION_BLOCKS    = 'blocks';
    const INSTRUCTION_REFERENCE = 'reference';

    /**
     * @var BlockFactoryInterface
     */
    protected $blockFactory;

    /**
     * @var BlockPoolInterface
     */
    protected $blockPool;

    /**
     * BlocksGenerator constructor.
     * @param BlockFactoryInterface $blockFactory
     * @param BlockPoolInterface $blockPool
     */
    public function __construct(
        BlockFactoryInterface $blockFactory,
        BlockPoolInterface $blockPool
    ) {
        $this->blockFactory = $blockFactory;
        $this->blockPool = $blockPool;
    }

    /**
     * @inheritDoc
     */
    public function generate(Config $layoutStructure)
    {
        $blocks     = $layoutStructure->get(self::INSTRUCTION_BLOCKS, new Config([]));
        $references = $layoutStructure->get(self::INSTRUCTION_REFERENCE);

        $this->generateBlocks($blocks, $references);
    }

    /**
     * @param Config $blocks
     * @param Config $references
     * @param string $parentId
     */
    private function generateBlocks(Config $blocks, Config $references = null, $parentId = null)
    {
        foreach ($blocks as $blockId => $specs) {
            if (null !== $references && $blockReference = $references->get($blockId)) {
                $specs->merge($blockReference);
            }
            if ($this->isRemoved($specs)) {
                continue;
            }
            if ($children = $specs->get(self::INSTRUCTION_BLOCKS)) {
                $this->generateBlocks($children, $references, $blockId);
            }
            if ($block = $this->blockPool->get($blockId)) {
                $this->blockFactory->configure($block, $specs->toArray());
            } else {
                $block = $this->blockFactory->createBlock($blockId, $specs->toArray());
            }
            if (null !== $parentId) {
                $block->setOption('has_parent', true);
                if (!$block->getOption('parent')) {
                    $block->setOption('parent', $parentId);
                }
            }
            $this->blockPool->add($blockId, $block);
        }
    }

    /**
     * @param Config $specs
     * @return boolean
     */
    private function isRemoved(Config $specs)
    {
        if ($remove = $specs->get('remove')) {
            return filter_var($remove, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }
        return false;
    }
}
