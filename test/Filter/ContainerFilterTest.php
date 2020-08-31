<?php

/**
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

declare(strict_types=1);

namespace ConLayoutTest\Filter;

use ConLayout\Filter\ContainerFilter;
use Laminas\View\Helper\ViewModel;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplateMapResolver;
use PHPUnit\Framework\TestCase;

/**
 * Class ContainerFilterTest
 * @package ConLayoutTest\Filter
 */
class ContainerFilterTest extends TestCase
{
    /**
     * @var ContainerFilter
     */
    protected $containerFilter;

    /**
     * @var ViewModel
     */
    protected $currentViewModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->currentViewModel = new \Laminas\View\Model\ViewModel();

        $phpRenderer = new PhpRenderer();
        $phpRenderer->setResolver(
            new TemplateMapResolver([
                'container' => __DIR__ . '/../_files/view/container.phtml',
                'container-2' => __DIR__ . '/../_files/view/container-2.phtml'
            ])
        );
        /** @var ViewModel $viewModelHelper */
        $viewModelHelper = $phpRenderer->plugin('viewModel');
        $viewModelHelper->setCurrent($this->currentViewModel);

        $this->containerFilter = new ContainerFilter($phpRenderer);
    }

    public function testContentIsWrappedWithString()
    {
        $this->currentViewModel->setOption('container', '<div class="wrapper">%s</div>');
        $value = '<span class="my-content">lorem ipsum</span>';

        $filtered = $this->containerFilter->filter($value);
        $expected = '<div class="wrapper">' . $value . '</div>';

        $this->assertEquals($expected, $filtered);
    }

    public function testContentIsWrappedWithTemplate()
    {
        $this->currentViewModel->setOption('container', 'container');
        $value = '<div class="header"><img src="logo.png"></div>';

        $filtered = $this->containerFilter->filter($value);
        $expected = '<div class="container-tpl">' . $value . '</div>';

        $this->assertEquals($expected, $filtered);
    }

    public function testContentIsWrappedWithMultipleTemplates()
    {
        $this->currentViewModel->setOption('container', 'container-2,container');
        $value = '<span>Lorem Ipsum</span>';

        $filtered = $this->containerFilter->filter($value);
        $expected = '<div class="container-tpl"><div class="container-2">' . $value . '</div></div>';

        $this->assertEquals($expected, $filtered);
    }

    public function testNoWrappingIfContainerIsEmpty()
    {
        $value = 'Lorem Ipsum';

        $filtered = $this->containerFilter->filter($value);
        $expected = $value;

        $this->assertEquals($expected, $filtered);
    }
}
