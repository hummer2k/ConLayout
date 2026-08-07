<?php

declare(strict_types=1);

namespace ConLayout\Updater\Collector\Element;

use ConLayout\Generator\BlocksGenerator;

final class ReferenceElement extends AbstractElement
{
    public const NAME = 'reference';

    public function name(): string
    {
        return self::NAME;
    }

    public function instruction(): string
    {
        return BlocksGenerator::INSTRUCTION_REFERENCE;
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(array $spec): array
    {
        if (!$this->isTyped($spec)) {
            return $spec;
        }
        $referenceId = $spec[self::ATTRIBUTE_ID];
        return [$referenceId => $this->normalizeBlockSpec($spec, self::NAME)];
    }

    private function isTyped(array $entry): bool
    {
        return isset($entry[self::ATTRIBUTE_ID]) && is_string($entry[self::ATTRIBUTE_ID]);
    }
}
