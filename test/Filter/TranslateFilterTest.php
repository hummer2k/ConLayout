<?php

declare(strict_types=1);

namespace ConLayoutTest\Filter;

use ConLayout\Filter\TranslateFilter;
use PHPUnit\Framework\TestCase;
use Laminas\I18n\Translator\TranslatorInterface;

class TranslateFilterTest extends TestCase
{
    public function testFilterTranslates(): void
    {
        $expected = 'translated';
        $translator = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $translator->method('translate')
            ->willReturn($expected);

        $filter = new TranslateFilter($translator);
        $this->assertSame($expected, $filter->filter('input'));
    }
}
