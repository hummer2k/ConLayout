<?php

declare(strict_types=1);

namespace ConLayout\Layout;

use ConLayout\Block\BlockRegistryInterface;
use ConLayout\Generator\GeneratorInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\Stdlib\PriorityList;
use Laminas\View\Model\ModelInterface;

class Layout implements
    EventManagerAwareInterface,
    LayoutInterface
{
    use EventManagerAwareTrait;

    protected mixed $eventIdentifier = null;

    protected bool $blocksInjected = false;

    protected PriorityList $generators;

    protected array $loadedGenerators = [];

    protected bool $isLoaded = false;

    public function __construct(
        private readonly LayoutUpdaterInterface $updater,
        private readonly BlockRegistryInterface $blockRegistry,
    ) {
        $this->generators = new PriorityList();
        $this->setEventManager(new EventManager());
    }

    public function getRoot(): ?ModelInterface
    {
        return $this->getBlock(self::BLOCK_ID_ROOT);
    }

    public function setRoot(ModelInterface $root): static
    {
        $this->blockRegistry->add(self::BLOCK_ID_ROOT, $root);
        return $this;
    }

    public function load(): static
    {
        if (false === $this->isLoaded) {
            $this->getEventManager()->trigger(
                __FUNCTION__ . '.pre',
                $this,
                [],
            );

            $this->generate();
            $this->injectBlocks();
            $this->isLoaded = true;

            $this->getEventManager()->trigger(
                __FUNCTION__ . '.post',
                $this,
                [],
            );
        }
        return $this;
    }

    public function generate(array $generators = []): static
    {
        $layoutStructure = $this->updater->getLayoutStructure();
        foreach ($this->generators as $name => $generator) {
            if (
                $generator instanceof GeneratorInterface
                && is_string($name)
                && !$this->isGeneratorLoaded($name)
                && (
                    empty($generators)
                    || isset($generators[$name])
                )
            ) {
                $generator->generate($layoutStructure);
                $this->loadedGenerators[$name] = true;
            }
        }
        return $this;
    }

    private function isGeneratorLoaded(string $name): bool
    {
        return isset($this->loadedGenerators[$name]);
    }

    public function injectBlocks(): void
    {
        if (false === $this->blocksInjected) {
            $this->blockRegistry->sort();
            $blocks = $this->blockRegistry->getAll();
            foreach ($blocks as $blockId => $block) {
                if (
                    $this->isAllowed($blockId, $block)
                    && $blockId !== self::BLOCK_ID_ROOT
                ) {
                    [$parent, $captureTo] = $this->getCaptureTo($block);
                    if ($parentBlock = $this->getBlock($parent)) {
                        $parentBlock->addChild($block, $captureTo);
                        $block->setVariable('parent', $parentBlock);
                    }
                }
            }
            $this->blocksInjected = true;
        }
    }

    private function isAllowed(string $blockId, ModelInterface $block): bool
    {
        $result = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'blockId' => $blockId,
                'block' => $block,
            ],
        );
        if ($result->stopped()) {
            return (bool) $result->last();
        }
        return true;
    }

    public function addBlock(string $blockId, ModelInterface $block): static
    {
        $this->blockRegistry->add($blockId, $block);
        return $this;
    }

    public function removeBlock(string $blockId): static
    {
        $this->blockRegistry->remove($blockId);
        return $this;
    }

    /**
     * retrieve parent and captureTo as array, e.g.: [ 'layout', 'content' ]
     * so we are able to list() blockId and captureTo values
     */
    protected function getCaptureTo(ModelInterface $block): array
    {
        $captureTo = $block->captureTo();
        if ($parent = $block->getVariable('parentId')) {
            $captureTo = explode(self::CAPTURE_TO_DELIMITER, $captureTo);
            return [
                $parent,
                end($captureTo),
            ];
        }
        if (str_contains($captureTo, self::CAPTURE_TO_DELIMITER)) {
            return explode(self::CAPTURE_TO_DELIMITER, $captureTo);
        }
        return [
            self::BLOCK_ID_ROOT,
            $captureTo,
        ];
    }

    /**
     * retrieve single block by its id
     *
     * @return  false|ModelInterface
     */
    public function getBlock(string $blockId): ?ModelInterface
    {
        return $this->blockRegistry->get($blockId);
    }

    /**
     * @return ModelInterface[]
     */
    public function getBlocks(): array
    {
        return $this->blockRegistry->getAll();
    }

    /**
     * @inheritDoc
     */
    public function attachGenerator(string $name, GeneratorInterface $generator, int $priority = 1): static
    {
        $this->generators->insert($name, $generator, $priority);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function detachGenerator(string $name): static
    {
        $this->generators->remove($name);
        return $this;
    }
}
