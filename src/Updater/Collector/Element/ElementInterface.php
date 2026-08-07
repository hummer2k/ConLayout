<?php

declare(strict_types=1);

namespace ConLayout\Updater\Collector\Element;

interface ElementInterface
{
    /**
     * The typed XML element name, e.g. "block" or "css".
     */
    public function name(): string;

    /**
     * The legacy instruction key the normalized element is merged into,
     * e.g. "blocks", "helpers" or "reference".
     */
    public function instruction(): string;

    /**
     * Normalizes a single typed element spec into a fragment keyed like the
     * legacy instruction structure.
     *
     * @param array<string, mixed> $spec
     * @return array<string, mixed>
     */
    public function normalize(array $spec): array;
}
