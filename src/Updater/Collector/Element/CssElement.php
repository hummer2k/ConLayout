<?php

declare(strict_types=1);

namespace ConLayout\Updater\Collector\Element;

use ConLayout\Generator\ViewHelperGenerator;

final class CssElement extends AbstractElement
{
    public const NAME = 'css';

    private const HEADLINK_HELPER = 'headLink';

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

        if (array_key_exists('src', $spec)) {
            $spec['href'] = $spec['src'];
            unset($spec['src']);
        }

        return [self::HEADLINK_HELPER => [$id => $spec]];
    }
}
