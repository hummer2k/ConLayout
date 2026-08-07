<?php

declare(strict_types=1);

namespace ConLayoutTest\Filter;

use ConLayout\Filter\DebugFilter;
use ConLayoutTest\AbstractTestCase;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\ViewModel as ModelHelper;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\AggregateResolver;

class DebugFilterTest extends AbstractTestCase
{
    public function testMarkupContainsCommentsWithDebugInfo(): void
    {
        $block = new ViewModel();
        $block->setVariable('blockId', 'the.block');

        $viewModelHelper = new ModelHelper();
        $viewModelHelper->setCurrent($block);

        $phpRenderer = new PhpRenderer(
            new HelperPluginManager(new ServiceManager()),
            new AggregateResolver(),
            false,
        );

        $filter = new DebugFilter($phpRenderer, $viewModelHelper);

        $html = '<div></div>';
        $filteredHtml = $filter->filter($html);

        $this->assertStringContainsString('<!--[the.block]', $filteredHtml);
    }
}
