<?php

declare(strict_types=1);

namespace ConLayout;

use ConLayout\Block\BlockRegistryInterface;
use ConLayout\Generator\GeneratorInterface;
use ConLayout\Handle\Handle;
use ConLayout\Handle\HandleInterface;
use ConLayout\Layout\LayoutInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
use Laminas\Config\Config;
use Laminas\View\Model\ModelInterface;

readonly class LayoutManager implements
    LayoutInterface,
    LayoutUpdaterInterface
{
    public function __construct(
        private LayoutInterface $layout,
        private LayoutUpdaterInterface $updater,
        private BlockRegistryInterface $blockPool,
    ) {}

    /**
     *
     * @return false|ModelInterface
     */
    public function getBlock(string $blockId): ModelInterface|false
    {
        return $this->blockPool->get($blockId);
    }

    public function addBlock(string $blockId, ModelInterface $block): static
    {
        $this->blockPool->add($blockId, $block);
        return $this;
    }

    public function removeBlock(string $blockId): static
    {
        $this->blockPool->remove($blockId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLayoutStructure(): Config
    {
        return $this->updater->getLayoutStructure();
    }

    /**
     * @inheritDoc
     */
    public function addHandle(HandleInterface $handle): static
    {
        $this->updater->addHandle($handle);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setArea(string $area): static
    {
        $this->updater->setArea($area);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getArea(): string
    {
        return $this->updater->getArea();
    }

    public function setHandles(array $handles): static
    {
        $this->updater->setHandles($handles);
        return $this;
    }

    public function getHandles(bool $asObject = false): array
    {
        return $this->updater->getHandles($asObject);
    }

    public function removeHandle(string $handleName): static
    {
        $this->updater->removeHandle($handleName);
        return $this;
    }

    /**
     *
     * @return ModelInterface[]
     */
    public function getBlocks(): array
    {
        return $this->blockPool->get();
    }

    public function load(): static
    {
        $this->layout->load();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoot(): ModelInterface|false
    {
        return $this->layout->getRoot();
    }

    /**
     * @inheritDoc
     */
    public function setRoot(ModelInterface $root): static
    {
        $this->layout->setRoot($root);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function injectBlocks(): void
    {
        $this->layout->injectBlocks();
    }

    /**
     * @inheritDoc
     */
    public function attachGenerator($name, GeneratorInterface $generator, $priority = 1): static
    {
        $this->layout->attachGenerator($name, $generator, $priority);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function detachGenerator($name): static
    {
        $this->layout->detachGenerator($name);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function generate(array $generators = []): static
    {
        $this->layout->generate($generators);
        return $this;
    }
}
