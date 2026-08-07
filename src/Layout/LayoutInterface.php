<?php

declare(strict_types=1);

namespace ConLayout\Layout;

use ConLayout\Generator\GeneratorInterface;
use Laminas\View\Model\ModelInterface;

interface LayoutInterface
{
    /**
     * block id of root view model
     */
    public const string BLOCK_ID_ROOT = 'root';

    /**
     * block id of view model returned by controller
     */
    public const string BLOCK_ID_MAIN_CONTENT = 'main.content';

    /**
     * delimiter blockId::captureTo
     */
    public const string CAPTURE_TO_DELIMITER = '::';

    /**
     * retrieve single block by block id
     *
     * @return false|ModelInterface
     */
    public function getBlock(string $blockId): ?ModelInterface;

    /**
     * retrieve the root view model
     *
     * @return false|ModelInterface
     */
    public function getRoot(): ?ModelInterface;

    /**
     * retrieve all blocks
     *
     * @return ModelInterface[]
     */
    public function getBlocks(): array;

    /**
     * add a single block
          */
    public function addBlock(string $blockId, ModelInterface $block): static;

    /**
     * removes a single block
          */
    public function removeBlock(string $blockId): static;

    /**
     * @param array $generators load only given generators or all if empty
     */
    public function generate(array $generators = []): static;

    /**
     * inject blocks/build view model tree
     */
    public function injectBlocks(): void;

    /**
     * load the layout
          */
    public function load(): static;

    /**
     * set root view model/layout
          */
    public function setRoot(ModelInterface $root): static;

    public function attachGenerator(string $name, GeneratorInterface $generator, int $priority = 1): static;

    /**
     * removes a generator
          */
    public function detachGenerator(string $name): static;
}
