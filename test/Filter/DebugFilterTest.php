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
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;

class DebugFilterTest extends AbstractTest
{
    public function testMarkupContainsCommentsWithDebugInfo()
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
