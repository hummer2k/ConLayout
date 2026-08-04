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
use ConLayout\View\Helper\Proxy\HeadLinkProxy;
use ConLayout\View\PhpRendererDelegatorFactory;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;

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

    public function testPhpRendererFilterChain(): void
    {
        $sm = Bootstrap::getServiceManager();

        $phpRenderer = $sm->get(PhpRenderer::class);
        $this->assertInstanceOf(PhpRenderer::class, $phpRenderer);
        $this->assertCount(1, $phpRenderer->getFilterChain()->getFilters());
    }

    public function testPhpRendererDelegatorFactory(): void
    {
        $renderer = new PhpRenderer();

        $container = new ServiceManager();
        $container->setService(ModuleOptions::class, new ModuleOptions());

        $factory = new PhpRendererDelegatorFactory();
        $result = $factory($container, PhpRenderer::class, fn() => $renderer);

        $this->assertSame($renderer, $result);
        $this->assertCount(1, $result->getFilterChain()->getFilters());

        $debugContainer = new ServiceManager();
        $debugContainer->setService(ModuleOptions::class, new ModuleOptions(['debug' => true]));

        $debugResult = $factory($debugContainer, PhpRenderer::class, fn() => new PhpRenderer());
        $this->assertCount(2, $debugResult->getFilterChain()->getFilters());
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

    public function testViewHelperProxy(): void
    {
        $sm = Bootstrap::getServiceManager();

        /** @var HelperPluginManager $helperPluginManager */
        $helperPluginManager = $sm->get('ViewHelperManager');

        $proxy = $helperPluginManager->get(HeadLinkProxy::class);
        $this->assertInstanceOf(HeadLinkProxy::class, $proxy);
    }
}
