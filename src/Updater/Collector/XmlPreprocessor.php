<?php

declare(strict_types=1);

namespace ConLayout\Updater\Collector;

use ConLayout\Updater\Collector\Element\BlockElement;
use ConLayout\Updater\Collector\Element\CssElement;
use ConLayout\Updater\Collector\Element\ElementInterface;
use ConLayout\Updater\Collector\Element\JsElement;
use ConLayout\Updater\Collector\Element\ReferenceElement;
use ConLayout\Updater\Collector\Element\TitleElement;
use Laminas\Config\Config;

/**
 * Normalizes the typed XML layout dialect into the keyed structure the legacy
 * syntax produces, so both dialects merge identically per handle.
 *
 * Each typed element is handled by a class implementing ElementInterface;
 * new elements can be added via the "con-layout.xml_preprocessor.elements"
 * config without touching this class.
 */
final class XmlPreprocessor
{
    /**
     * @param iterable<ElementInterface> $elements
     */
    public function __construct(
        private readonly iterable $elements = [
            new BlockElement(),
            new CssElement(),
            new JsElement(),
            new TitleElement(),
            new ReferenceElement(),
        ],
    ) {}

    public function process(Config $config): Config
    {
        $data = $config->toArray();

        foreach ($this->elements as $element) {
            if (array_key_exists($element->name(), $data)) {
                $data = $this->apply($element, $data);
            }
        }

        return new Config($data, true);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function apply(ElementInterface $element, array $data): array
    {
        $name = $element->name();
        $instruction = $element->instruction();
        $seed = $instruction === $name
            ? []
            : (array) ($data[$instruction] ?? []);
        $target = new Config($seed, true);
        foreach ($this->normalizeList($data[$name]) as $spec) {
            $target->merge(new Config($element->normalize($spec), true));
        }
        unset($data[$name]);
        $data[$instruction] = $target->toArray();
        return $data;
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
}
