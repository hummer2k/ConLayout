<?php

declare(strict_types=1);

namespace ConLayout\Updater\Collector\Element;

use ConLayout\Exception\InvalidArgumentException;
use ConLayout\Generator\BlocksGenerator;
use Laminas\Config\Config;

abstract class AbstractElement implements ElementInterface
{
    public const string ATTRIBUTE_ID   = 'id';
    public const string ATTRIBUTE_NAME = 'name';

    /**
     * block variables holding insertion metadata, read by the BlockRegistry
     */
    private const array BLOCK_METADATA = [
        'order',
        'parentId',
        'before',
        'after',
    ];

    protected function requireString(array $spec, string $attribute, string $element): string
    {
        if (!isset($spec[$attribute]) || !is_string($spec[$attribute]) || '' === $spec[$attribute]) {
            throw new InvalidArgumentException(sprintf(
                'Layout XML: <%s> requires a non-empty "%s" attribute.',
                $element,
                $attribute,
            ));
        }
        return $spec[$attribute];
    }

    protected function isEnabled(array $spec, string $attribute): bool
    {
        if (!array_key_exists($attribute, $spec)) {
            return false;
        }
        return (bool) filter_var($spec[$attribute], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    /**
     * @return list<array>
     */
    protected function normalizeList(mixed $entries): array
    {
        if (!is_array($entries)) {
            return [];
        }
        if (array_key_exists(0, $entries)) {
            return array_values($entries);
        }
        return [$entries];
    }

    /**
     * Normalizes a block spec: drops the id, moves insertion metadata into
     * variables and merges nested typed/legacy children into a blocks child.
     * Shared by the block and reference elements.
     *
     * @param array<string, mixed> $spec
     * @return array<string, mixed>
     */
    protected function normalizeBlockSpec(array $spec, string $elementName): array
    {
        $this->requireString($spec, self::ATTRIBUTE_ID, $elementName);
        unset($spec[self::ATTRIBUTE_ID]);

        $nested = $spec[BlockElement::NAME] ?? null;
        unset($spec[BlockElement::NAME]);
        $legacyChildren = $spec[BlocksGenerator::INSTRUCTION_BLOCKS] ?? null;
        unset($spec[BlocksGenerator::INSTRUCTION_BLOCKS]);

        $variables = $spec['variables'] ?? [];
        if (!is_array($variables)) {
            $variables = [];
        }
        unset($spec['variables']);
        foreach (self::BLOCK_METADATA as $metadata) {
            if (array_key_exists($metadata, $spec)) {
                $variables[$metadata] = $spec[$metadata];
                unset($spec[$metadata]);
            }
        }
        if ([] !== $variables) {
            $spec['variables'] = $variables;
        }

        if (null !== $nested || null !== $legacyChildren) {
            $spec[BlocksGenerator::INSTRUCTION_BLOCKS] = $this->mergeBlocks(
                (array) ($legacyChildren ?? []),
                $nested,
            );
        }

        return $spec;
    }

    /**
     * @return array<string, mixed>
     */
    private function mergeBlocks(array $legacy, mixed $typed): array
    {
        $blocks = new Config($legacy, true);
        foreach ($this->normalizeList($typed) as $spec) {
            $blocks->merge(new Config($this->normalize($spec), true));
        }
        return $blocks->toArray();
    }
}
