<?php

declare(strict_types=1);

namespace ConLayout\Updater\Collector;

use ConLayout\Exception\InvalidArgumentException;
use ConLayout\Generator\BlocksGenerator;
use ConLayout\Generator\ViewHelperGenerator;
use Laminas\Config\Config;

/**
 * Normalizes the typed XML layout dialect into the keyed structure the legacy
 * syntax produces, so both dialects merge identically per handle.
 */
final class XmlPreprocessor
{
    public const ELEMENT_BLOCK       = 'block';
    public const ELEMENT_VIEW_HELPER = 'view_helper';
    public const ATTRIBUTE_ID        = 'id';
    public const ATTRIBUTE_NAME      = 'name';

    /**
     * block variables holding insertion metadata, read by the BlockRegistry
     */
    private const BLOCK_METADATA = [
        'order',
        'parent',
        'before',
        'after',
    ];

    public function process(Config $config): Config
    {
        $data = $config->toArray();

        if (array_key_exists(self::ELEMENT_BLOCK, $data)) {
            $data[BlocksGenerator::INSTRUCTION_BLOCKS] = $this->mergeBlocks(
                (array) ($data[BlocksGenerator::INSTRUCTION_BLOCKS] ?? []),
                $data[self::ELEMENT_BLOCK],
            );
        }
        unset($data[self::ELEMENT_BLOCK]);

        if (array_key_exists(self::ELEMENT_VIEW_HELPER, $data)) {
            $helpers = new Config((array) ($data[ViewHelperGenerator::INSTRUCTION] ?? []), true);
            foreach ($this->normalizeList($data[self::ELEMENT_VIEW_HELPER]) as $spec) {
                $name = $this->requireString($spec, self::ATTRIBUTE_NAME, self::ELEMENT_VIEW_HELPER);
                $id = $this->requireString($spec, self::ATTRIBUTE_ID, self::ELEMENT_VIEW_HELPER);
                unset($spec[self::ATTRIBUTE_NAME], $spec[self::ATTRIBUTE_ID]);
                $helpers->merge(new Config([$name => [$id => $spec]], true));
            }
            $data[ViewHelperGenerator::INSTRUCTION] = $helpers->toArray();
        }
        unset($data[self::ELEMENT_VIEW_HELPER]);

        if (array_key_exists(BlocksGenerator::INSTRUCTION_REFERENCE, $data)) {
            $references = new Config([], true);
            foreach ($this->normalizeList($data[BlocksGenerator::INSTRUCTION_REFERENCE]) as $entry) {
                if ($this->isTypedBlock($entry)) {
                    $references->merge(new Config([
                        $entry[self::ATTRIBUTE_ID] => $this->normalizeBlock($entry),
                    ], true));
                } else {
                    $references->merge(new Config((array) $entry, true));
                }
            }
            $data[BlocksGenerator::INSTRUCTION_REFERENCE] = $references->toArray();
        }

        return new Config($data, true);
    }

    /**
     * @return array<string, mixed>
     */
    private function mergeBlocks(array $legacy, mixed $typed): array
    {
        $blocks = new Config($legacy, true);
        foreach ($this->normalizeList($typed) as $spec) {
            $blockId = $this->requireString($spec, self::ATTRIBUTE_ID, self::ELEMENT_BLOCK);
            $blocks->merge(new Config([$blockId => $this->normalizeBlock($spec)], true));
        }
        return $blocks->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeBlock(array $spec): array
    {
        $blockId = $this->requireString($spec, self::ATTRIBUTE_ID, self::ELEMENT_BLOCK);
        unset($spec[self::ATTRIBUTE_ID]);

        $nested = $spec[self::ELEMENT_BLOCK] ?? null;
        unset($spec[self::ELEMENT_BLOCK]);
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
     * @return list<array>
     */
    private function normalizeList(mixed $entries): array
    {
        if (!is_array($entries)) {
            return [];
        }
        if (array_key_exists(0, $entries)) {
            return array_values($entries);
        }
        return [$entries];
    }

    private function isTypedBlock(array $entry): bool
    {
        return isset($entry[self::ATTRIBUTE_ID]) && is_string($entry[self::ATTRIBUTE_ID]);
    }

    private function requireString(array $spec, string $attribute, string $element): string
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
}
