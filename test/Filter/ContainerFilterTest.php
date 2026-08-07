<?php

declare(strict_types=1);

namespace ConLayoutTest\Filter;

use ConLayout\Filter\ContainerFilter;
use Laminas\View\Helper\ViewModel;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel as Model;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplateMapResolver;
use PHPUnit\Framework\TestCase;

/**
 * Class ContainerFilterTest
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
        $this->currentViewModel = new Model();

        $helpers = new HelperPluginManager(new \Laminas\ServiceManager\ServiceManager());
        $phpRenderer = new PhpRenderer(
            $helpers,
            new TemplateMapResolver([
                'container' => __DIR__ . '/../_files/view/container.phtml',
                'container-2' => __DIR__ . '/../_files/view/container-2.phtml',
            ]),
            false,
        );
        $viewModelHelper = new ViewModel();
        $viewModelHelper->setCurrent($this->currentViewModel);

        $this->containerFilter = new ContainerFilter($phpRenderer, $viewModelHelper);
    }

    public function testContentIsWrappedWithString(): void
    {
        $this->currentViewModel->setVariable('blockId', 'the.block');
        $this->currentViewModel->setVariable('container', '<div class="wrapper">%s</div>');
        $value = '<span class="my-content">lorem ipsum</span>';

        $filtered = $this->containerFilter->filter($value);
        $expected = '<div class="wrapper">' . $value . '</div>';

        $this->assertEquals($expected, $filtered);
    }

    public function testContentIsWrappedWithTemplate(): void
    {
        $this->currentViewModel->setVariable('blockId', 'the.block');
        $this->currentViewModel->setVariable('container', 'container');
        $value = '<div class="header"><img src="logo.png"></div>';

        $filtered = $this->containerFilter->filter($value);
        $expected = '<div class="container-tpl">' . $value . '</div>';

        $this->assertEquals($expected, $filtered);
    }

    public function testContentIsWrappedWithMultipleTemplates(): void
    {
        $this->currentViewModel->setVariable('blockId', 'the.block');
        $this->currentViewModel->setVariable('container', 'container-2,container');
        $value = '<span>Lorem Ipsum</span>';

        $filtered = $this->containerFilter->filter($value);
        $expected = '<div class="container-tpl"><div class="container-2">' . $value . '</div></div>';

        $this->assertEquals($expected, $filtered);
    }

    public function testNoWrappingIfContainerIsEmpty(): void
    {
        $value = 'Lorem Ipsum';

        $filtered = $this->containerFilter->filter($value);
        $expected = $value;

        $this->assertEquals($expected, $filtered);
    }
}
