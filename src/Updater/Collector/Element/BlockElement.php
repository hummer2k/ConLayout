<?php

declare(strict_types=1);

namespace ConLayout\Updater\Collector\Element;

use ConLayout\Generator\BlocksGenerator;

final class BlockElement extends AbstractElement
{
    public const NAME = 'block';

    public function name(): string
    {
        return self::NAME;
    }

    public function instruction(): string
    {
        return BlocksGenerator::INSTRUCTION_BLOCKS;
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(array $spec): array
    {
        $blockId = $this->requireString($spec, self::ATTRIBUTE_ID, self::NAME);
        return [$blockId => $this->normalizeBlockSpec($spec, self::NAME)];
    }
}
