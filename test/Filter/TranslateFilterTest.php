<?php

namespace ConLayoutTest\Filter;

use ConLayout\Filter\TranslateFilter;
use PHPUnit\Framework\TestCase;
use Zend\I18n\Translator\TranslatorInterface;

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class TranslateFilterTest extends TestCase
{
    public function testFilterTranslates()
    {
        $expected = 'translated';
        $translator = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $translator->method('translate')
            ->willReturn($expected);

        $filter = new TranslateFilter($translator);
        $this->assertSame($expected, $filter->filter('input'));
    }
}
