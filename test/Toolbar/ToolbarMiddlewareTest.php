<?php

declare(strict_types=1);

namespace ConLayoutTest\Toolbar;

use ConLayout\Block\BlockRegistryInterface;
use ConLayout\Handle\Handle;
use ConLayout\LayoutManager;
use ConLayout\Options\ModuleOptions;
use ConLayout\Toolbar\ToolbarData;
use ConLayout\Toolbar\ToolbarMiddleware;
use ConLayout\Toolbar\ToolbarMiddlewareFactory;
use ConLayout\Updater\Collector\FilesystemCollector;
use ConLayoutTest\Bootstrap;
use Laminas\Config\Config;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplateMapResolver;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionProperty;

class ToolbarMiddlewareTest extends TestCase
{
    public function testInjectsToolbarIntoHtmlResponse(): void
    {
        $root = new ViewModel();
        $root->setVariable('blockId', 'root');
        $root->setTemplate('base');

        $header = new ViewModel();
        $header->setVariable('blockId', 'header');
        $header->setTemplate('layout::header');
        $header->setCaptureTo('header');
        $root->addChild($header);

        $layoutManager = $this->createMock(LayoutManager::class);
        $layoutManager->method('getRoot')->willReturn($root);
        $layoutManager->method('getHandles')->willReturn([
            new Handle('default', -1),
            new Handle('home', 10),
        ]);
        $layoutManager->method('getArea')->willReturn('frontend');
        $layoutManager->method('getLayoutStructure')->willReturn(new Config([
            'blocks' => ['root' => [], 'header' => []],
        ], true));
        $layoutManager->method('getBlocks')->willReturn([
            'root'   => $root,
            'header' => $header,
            'orphan' => new ViewModel(),
        ]);

        $filesystemCollector = $this->createMock(FilesystemCollector::class);
        $filesystemCollector->method('getCollectedFiles')->willReturn([
            'default' => ['/path/to/default.xml'],
        ]);

        $blockRegistry = $this->createMock(BlockRegistryInterface::class);
        $blockRegistry->method('getRemovedBlocks')->willReturn(['removed-block']);

        $toolbarData = new ToolbarData(
            $layoutManager,
            $filesystemCollector,
            $blockRegistry,
            new ModuleOptions(['layout_template' => 'base', 'default_area' => 'frontend']),
        );

        $middleware = new ToolbarMiddleware($toolbarData, $this->getRenderer(), true);

        $response = $middleware->process(
            new ServerRequest(),
            $this->getHandler(new HtmlResponse('<html><body><h1>Hi</h1></body></html>')),
        );

        $body = (string) $response->getBody();
        $this->assertStringContainsString('conlayout-toolbar', $body);
        $this->assertStringContainsString('default', $body);
        $this->assertStringContainsString('home', $body);
        $this->assertStringContainsString('/path/to/default.xml', $body);
        $this->assertStringContainsString('base', $body);
        $this->assertStringContainsString('header', $body);
        $this->assertStringContainsString('layout::header', $body);
        $this->assertStringContainsString('frontend', $body);
        $this->assertStringContainsString('orphan', $body);
        $this->assertStringContainsString('removed-block', $body);
        $this->assertStringContainsString('Duration', $body);
        $this->assertStringContainsString(
            '<div id="conlayout-toolbar"',
            substr($body, 0, strpos($body, '</body>')),
        );
    }

    public function testAppendsToolbarWhenNoBodyTag(): void
    {
        $root = new ViewModel();
        $root->setVariable('blockId', 'root');
        $root->setTemplate('base');

        $layoutManager = $this->createMock(LayoutManager::class);
        $layoutManager->method('getRoot')->willReturn($root);

        $middleware = new ToolbarMiddleware(
            new ToolbarData($layoutManager),
            $this->getRenderer(),
            true,
        );

        $response = $middleware->process(
            new ServerRequest(),
            $this->getHandler(new HtmlResponse('<h1>Hi</h1>')),
        );

        $body = (string) $response->getBody();
        $this->assertStringContainsString('conlayout-toolbar', $body);
        $this->assertStringEndsWith('</div>', trim($body));
    }

    public function testSkipsWhenDisabled(): void
    {
        $layoutManager = $this->createMock(LayoutManager::class);

        $middleware = new ToolbarMiddleware(
            new ToolbarData($layoutManager),
            $this->getRenderer(),
            false,
        );

        $response = $middleware->process(
            new ServerRequest(),
            $this->getHandler(new HtmlResponse('<html><body><h1>Hi</h1></body></html>')),
        );

        $this->assertSame('<html><body><h1>Hi</h1></body></html>', (string) $response->getBody());
    }

    public function testSkipsOnNonHtmlResponse(): void
    {
        $layoutManager = $this->createMock(LayoutManager::class);

        $middleware = new ToolbarMiddleware(
            new ToolbarData($layoutManager),
            $this->getRenderer(),
            true,
        );

        $response = $middleware->process(
            new ServerRequest(),
            $this->getHandler(new JsonResponse(['foo' => 'bar'])),
        );

        $this->assertSame('{"foo":"bar"}', (string) $response->getBody());
    }

    public function testSkipsWhenNoLayoutRendered(): void
    {
        $layoutManager = $this->createMock(LayoutManager::class);
        $layoutManager->method('getRoot')->willReturn(null);

        $middleware = new ToolbarMiddleware(
            new ToolbarData($layoutManager),
            $this->getRenderer(),
            true,
        );

        $response = $middleware->process(
            new ServerRequest(),
            $this->getHandler(new HtmlResponse('<html><body><h1>Hi</h1></body></html>')),
        );

        $this->assertSame('<html><body><h1>Hi</h1></body></html>', (string) $response->getBody());
    }

    public function testFactory(): void
    {
        $factory = new ToolbarMiddlewareFactory();
        $instance = $factory(Bootstrap::getServiceManager(), ToolbarMiddleware::class);

        $this->assertInstanceOf(ToolbarMiddleware::class, $instance);
        $enabled = (new ReflectionProperty(ToolbarMiddleware::class, 'enabled'))->getValue($instance);
        $this->assertTrue($enabled);
    }

    protected function getRenderer(): PhpRenderer
    {
        $renderer = new PhpRenderer(
            new HelperPluginManager(new ServiceManager()),
            new TemplateMapResolver([
                'con-layout::toolbar' => __DIR__ . '/../../view/toolbar.phtml',
            ]),
            false,
        );
        return $renderer;
    }

    protected function getHandler(ResponseInterface $response): RequestHandlerInterface
    {
        return new class ($response) implements RequestHandlerInterface {
            public function __construct(private ResponseInterface $response) {}

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };
    }
}
