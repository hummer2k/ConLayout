<?php

declare(strict_types=1);

namespace ConLayoutTest\Filter;

use ConLayout\Filter\DebugFilter;
use ConLayoutTest\AbstractTestCase;
use Laminas\View\Helper\ViewModel as ModelHelper;
use Laminas\View\Model\ViewModel;

class DebugFilterTest extends AbstractTestCase
{
    public function testMarkupContainsCommentsWithDebugInfo(): void
    {
        $block = new ViewModel();
        $block->setVariable('blockId', 'the.block');

        $viewModelHelper = new ModelHelper();
        $viewModelHelper->setCurrent($block);

        $filter = new DebugFilter($viewModelHelper);

        $html = '<div></div>';
        $filteredHtml = $filter->filter($html);

        $this->assertStringContainsString('<!--[the.block]', $filteredHtml);
    }
}
