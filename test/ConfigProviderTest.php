<?php

declare(strict_types=1);

namespace ConLayoutTest;

use ConLayout\BlockManager;
use ConLayout\ConfigProvider;
use ConLayout\LayoutManager;
use ConLayout\Middleware\BodyClassMiddleware;
use ConLayout\Middleware\HandleExtractionMiddleware;
use ConLayout\Middleware\LayoutContextMiddleware;
use ConLayout\Middleware\LayoutMiddleware;
use ConLayout\Middleware\PrepareMainContentMiddleware;
use ConLayout\Options\ModuleOptions;
use ConLayout\Toolbar\ToolbarMiddleware;
use ConLayout\View\Helper\Layout;
use ConLayout\View\PhpRendererDelegatorFactory;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\ViewModel as ViewModelHelper;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\TemplateMapResolver;

class ConfigProviderTest extends AbstractTestCase
{
    public function testConfig(): void
    {
        $config = (new ConfigProvider())();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('dependencies', $config);
        $this->assertArrayHasKey('blocks', $config);
        $this->assertArrayHasKey('con-layout', $config);
        $this->assertArrayHasKey('templates', $config);
        $this->assertArrayHasKey('view_helpers', $config);
        $this->assertArrayHasKey('middleware', $config);
    }

    public function testMiddlewarePipeline(): void
    {
        $config = (new ConfigProvider())();

        $this->assertSame([
            ToolbarMiddleware::class,
            LayoutContextMiddleware::class,
            LayoutMiddleware::class,
            HandleExtractionMiddleware::class,
            BodyClassMiddleware::class,
            PrepareMainContentMiddleware::class,
        ], $config['middleware']);
    }

    public function testServiceManagerWiring(): void
    {
        $sm = Bootstrap::getServiceManager();

        $this->assertTrue($sm->has(BlockManager::class));
        $this->assertTrue($sm->has(LayoutManager::class));
        $this->assertTrue($sm->has(ModuleOptions::class));
        $this->assertTrue($sm->has(PhpRenderer::class));
        $this->assertInstanceOf(BlockManager::class, $sm->get(BlockManager::class));
        $this->assertInstanceOf(LayoutManager::class, $sm->get(LayoutManager::class));
        $this->assertInstanceOf(ModuleOptions::class, $sm->get(ModuleOptions::class));
    }

    public function testPhpRendererFilterWrapsContainerBlocks(): void
    {
        $sm = Bootstrap::getServiceManager();

        $phpRenderer = $sm->get(PhpRenderer::class);
        $this->assertInstanceOf(PhpRenderer::class, $phpRenderer);

        $block = new ViewModel();
        $block->setTemplate('con-layout::content');
        $block->setVariable('block_id', 'test.block');
        $block->setVariable('container', '<div class="wrapper">%s</div>');
        $block->setVariable('content', 'My content');

        $sm->get(HelperPluginManager::class)->get(ViewModelHelper::class)->setCurrent($block);

        $this->assertStringContainsString(
            '<div class="wrapper">My content</div>',
            $phpRenderer->render($block),
        );

        $plainBlock = new ViewModel();
        $plainBlock->setTemplate('con-layout::content');
        $plainBlock->setVariable('content', 'Plain content');
        $sm->get(HelperPluginManager::class)->get(ViewModelHelper::class)->setCurrent($plainBlock);

        $this->assertStringNotContainsString(
            '<div class="wrapper">',
            $phpRenderer->render($plainBlock),
        );
    }

    public function testPhpRendererDelegatorFactory(): void
    {
        $helpers = new HelperPluginManager(new ServiceManager());
        $viewModelHelper = $helpers->get(ViewModelHelper::class);
        $renderer = new PhpRenderer($helpers, new TemplateMapResolver([
            'con-layout::content' => __DIR__ . '/../view/blocks/content.phtml',
        ]), false);

        $container = new ServiceManager();
        $container->setService(ModuleOptions::class, new ModuleOptions());
        $container->setService(HelperPluginManager::class, $helpers);

        $factory = new PhpRendererDelegatorFactory();
        $result = $factory($container, PhpRenderer::class, fn() => $renderer);

        $this->assertSame($renderer, $result);

        $block = new ViewModel();
        $block->setTemplate('con-layout::content');
        $block->setVariable('content', 'content');
        $block->setVariable('block_id', 'test.block');
        $block->setVariable('container', '<div class="wrapper">%s</div>');
        $viewModelHelper->setCurrent($block);
        $this->assertSame(
            '<div class="wrapper">content</div>',
            $renderer->render($block),
        );

        $debugContainer = new ServiceManager();
        $debugContainer->setService(ModuleOptions::class, new ModuleOptions(['debug' => true]));
        $debugContainer->setService(HelperPluginManager::class, $helpers);

        $debugResult = $factory($debugContainer, PhpRenderer::class, fn() => new PhpRenderer(
            $helpers,
            new AggregateResolver(),
            false,
        ));
        $this->assertInstanceOf(PhpRenderer::class, $debugResult);
    }

    public function testLayoutViewHelper(): void
    {
        $sm = Bootstrap::getServiceManager();

        /** @var HelperPluginManager $helperPluginManager */
        $helperPluginManager = $sm->get('ViewHelperManager');
        $this->assertInstanceOf(HelperPluginManager::class, $helperPluginManager);

        $helper = $helperPluginManager->get('layout');
        $this->assertInstanceOf(Layout::class, $helper);
        $this->assertInstanceOf(LayoutManager::class, $helper());
    }
}
