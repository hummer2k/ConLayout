<?php

declare(strict_types=1);

namespace ConLayout\Updater\Collector\Element;

use ConLayout\Exception\InvalidArgumentException;
use ConLayout\Generator\ViewHelperGenerator;

final class TitleElement extends AbstractElement
{
    public const NAME = 'title';

    private const HEADTITLE_HELPER = 'headTitle';
    private const TITLE_PLACEMENTS = ['append', 'set', 'prepend'];
    private const TITLE_DEFAULT    = 'append';
    private const ELEMENT_TEXT     = '_';

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

        $placement = null;
        foreach (self::TITLE_PLACEMENTS as $candidate) {
            if (array_key_exists($candidate, $spec)) {
                if (null !== $placement) {
                    throw new InvalidArgumentException(sprintf(
                        'Layout XML: <%s> accepts only one of "%s" per element.',
                        self::NAME,
                        implode('", "', self::TITLE_PLACEMENTS),
                    ));
                }
                $placement = $candidate;
            }
        }
        unset($spec['append'], $spec['set'], $spec['prepend']);

        $value = trim((string) ($spec[self::ELEMENT_TEXT] ?? ''));
        unset($spec[self::ELEMENT_TEXT]);
        if ('' === $value) {
            throw new InvalidArgumentException(sprintf(
                'Layout XML: <%s> requires non-empty element content.',
                self::NAME,
            ));
        }
        $spec['value'] = $value;
        $spec['method'] = $placement ?? self::TITLE_DEFAULT;

        return [self::HEADTITLE_HELPER => [$id => $spec]];
    }
}
