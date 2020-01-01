<?php

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayoutTest\Filter;

use ConLayout\Filter\DebugFilter;
use ConLayoutTest\AbstractTest;
use Laminas\View\Helper\ViewModel as ModelHelper;
use Laminas\View\Model\ViewModel;

class DebugFilterTest extends AbstractTest
{
    public function testMarkupContainsCommentsWithDebugInfo()
    {
        $block = new ViewModel();
        $block->setOption('block_id', 'the.block');

        $viewModelHelper = new ModelHelper();
        $viewModelHelper->setCurrent($block);

        $filter = new DebugFilter($viewModelHelper);

        $html = '<div></div>';
        $filteredHtml = $filter->filter($html);

        $this->assertStringContainsString('<!--[the.block]', $filteredHtml);
    }
}
