<?php

declare(strict_types=1);

namespace ConLayout\Updater\Collector\Element;

use ConLayout\Generator\ViewHelperGenerator;

final class JsElement extends AbstractElement
{
    public const NAME = 'js';

    private const HEADSCRIPT_HELPER   = 'headScript';
    private const INLINESCRIPT_HELPER = 'inlineScript';

    public function name(): string
    {
        return self::NAME;
    }

    public function instruction(): string
    {
        return ViewHelperGenerator::INSTRUCTION;
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(array $spec): array
    {
        $id = $this->requireString($spec, self::ATTRIBUTE_ID, self::NAME);
        unset($spec[self::ATTRIBUTE_ID]);

        $helper = $this->isEnabled($spec, 'inline')
            ? self::INLINESCRIPT_HELPER
            : self::HEADSCRIPT_HELPER;
        unset($spec['inline']);

        return [$helper => [$id => $spec]];
    }
}
