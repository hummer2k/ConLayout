<?php

declare(strict_types=1);

namespace ConLayout\Generator;

use ConLayout\Block\BlockRegistryInterface;
use ConLayout\Block\Factory\BlockFactoryInterface;
use Laminas\Config\Config;

final readonly class BlocksGenerator implements GeneratorInterface
{
    public const NAME                  = 'blocks';
    public const INSTRUCTION_BLOCKS    = 'blocks';
    public const INSTRUCTION_REFERENCE = 'reference';

    public function __construct(
        private BlockFactoryInterface $blockFactory,
        private BlockRegistryInterface $blockRegistry,
    ) {}

    /**
     * @inheritDoc
     */
    public function generate(Config $layoutStructure): void
    {
        $blocks     = $layoutStructure->get(self::INSTRUCTION_BLOCKS, new Config([]));
        $references = $layoutStructure->get(self::INSTRUCTION_REFERENCE);

        $this->generateBlocks($blocks, $references);
    }

    private function generateBlocks(Config $blocks, ?Config $references = null, ?string $parentId = null): void
    {
        foreach ($blocks as $blockId => $specs) {
            if (!is_string($blockId)) {
                continue;
            }
            if (null !== $references && $blockReference = $references->get($blockId)) {
                $specs->merge($blockReference);
            }
            if ($this->isRemoved($specs)) {
                continue;
            }
            if ($children = $specs->get(self::INSTRUCTION_BLOCKS)) {
                $this->generateBlocks($children, $references, $blockId);
            }
            if ($block = $this->blockRegistry->get($blockId)) {
                $this->blockFactory->configure($block, $specs->toArray());
            } else {
                $block = $this->blockFactory->createBlock($blockId, $specs->toArray());
            }
            if (null !== $parentId) {
                $block->setVariable('hasParent', true);
                if (!$block->getVariable('parentId')) {
                    $block->setVariable('parentId', $parentId);
                }
            }
            $this->blockRegistry->add($blockId, $block);
        }
    }

    private function isRemoved(Config $specs): bool
    {
        if ($remove = $specs->get('remove')) {
            return filter_var($remove, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
        }
        return false;
    }
}
