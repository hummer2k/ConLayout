<?php

declare(strict_types=1);

namespace ConLayoutTest\Filter;

use ConLayout\Filter\DebugFilter;
use ConLayoutTest\AbstractTestCase;
use Laminas\View\Helper\ViewModel as ModelHelper;
use Laminas\View\Model\ViewModel;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;

class DebugFilterTest extends AbstractTestCase
{
    public function testMarkupContainsCommentsWithDebugInfo(): void
    {
        $block = new ViewModel();
        $block->setOption('block_id', 'the.block');

        $viewModelHelper = new ModelHelper();
        $viewModelHelper->setCurrent($block);

        $helpers = new HelperPluginManager(new ServiceManager());
        $helpers->setService('viewModel', $viewModelHelper);

        $phpRenderer = new PhpRenderer();
        $phpRenderer->setHelperPluginManager($helpers);

        $filter = new DebugFilter($phpRenderer);

        $html = '<div></div>';
        $filteredHtml = $filter->filter($html);

        $this->assertStringContainsString('<!--[the.block]', $filteredHtml);
    }
}
