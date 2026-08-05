<?php

declare(strict_types=1);

namespace ConLayoutTest\Middleware;

use ConLayout\Http\LayoutContext;
use ConLayout\Http\ViewModelResponse;
use ConLayout\Layout\LayoutInterface;
use ConLayout\LayoutManager;
use ConLayout\Middleware\LayoutMiddleware;
use ConLayout\Middleware\LayoutMiddlewareFactory;
use ConLayout\Options\ModuleOptions;
use ConLayoutTest\AbstractTestCase;
use ConLayoutTest\Bootstrap;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LayoutMiddlewareTest extends AbstractTestCase
{
    protected LayoutMiddleware $middleware;

    protected LayoutManager $layoutManager;

    protected LayoutContext $context;

    protected PhpRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->renderer = new PhpRenderer(
            new HelperPluginManager(new ServiceManager()),
            $this->getResolver(),
            false,
        );

        $this->layoutManager = new LayoutManager($this->layout, $this->layoutUpdater, $this->blockPool);
        $this->context = new LayoutContext($this->layoutManager);

        $this->middleware = new LayoutMiddleware(
            $this->layoutManager,
            $this->renderer,
            new ModuleOptions(),
            new HelperPluginManager(new ServiceManager()),
            'layout',
        );
    }

    public function testLoadLayout(): void
    {
        $viewModel = new ViewModel();
        $viewModel->setTemplate('widget1');

        $this->blockPool->add(LayoutInterface::BLOCK_ID_MAIN_CONTENT, $viewModel);

        $handler = $this->getHandler(new ViewModelResponse($viewModel));

        $result = $this->middleware->process($this->createRequest(), $handler);

        $this->assertTrue($this->layout->isLoaded());

        $this->assertSame(
            'layout',
            $this->layout->getBlock(LayoutInterface::BLOCK_ID_ROOT)->getTemplate(),
        );

        $this->assertInstanceOf(HtmlResponse::class, $result);
        $this->assertStringContainsString('<!doctype html>', (string) $result->getBody());
        $this->assertStringContainsString('My Widget', (string) $result->getBody());
    }

    public function testLoadLayoutWithTerminationModel(): void
    {
        $viewModel = new ViewModel();
        $viewModel->setTemplate('widget1');
        $viewModel->setTerminal(true);

        $handler = $this->getHandler(new ViewModelResponse($viewModel));

        $result = $this->middleware->process($this->createRequest(), $handler);

        $this->assertFalse($this->layout->isLoaded());

        $this->assertInstanceOf(HtmlResponse::class, $result);
        $this->assertStringContainsString('My Widget', (string) $result->getBody());
        $this->assertStringNotContainsString('<!doctype html>', (string) $result->getBody());
    }

    public function testHtmlResponse(): void
    {
        $content = new ViewModel();
        $content->setTemplate('widget-content');

        $this->blockPool->add(LayoutInterface::BLOCK_ID_MAIN_CONTENT, $content);

        $handler = $this->getHandler(new HtmlResponse('My widget content'));

        $result = $this->middleware->process($this->createRequest(), $handler);

        $this->assertTrue($this->layout->isLoaded());
        $this->assertInstanceOf(HtmlResponse::class, $result);
        $this->assertStringContainsString('<!doctype html>', (string) $result->getBody());
        $this->assertStringContainsString('My widget content', (string) $result->getBody());
    }

    public function testSkipOnNonHtmlResponse(): void
    {
        $response = new JsonResponse(['foo' => 'bar']);

        $handler = $this->getHandler($response);

        $result = $this->middleware->process($this->createRequest(), $handler);

        $this->assertFalse($this->layout->isLoaded());
        $this->assertSame('{"foo":"bar"}', (string) $result->getBody());
    }

    public function testSkipWhenDisabled(): void
    {
        $this->middleware = new LayoutMiddleware(
            $this->layoutManager,
            $this->renderer,
            new ModuleOptions(['enabled' => false]),
            new HelperPluginManager(new ServiceManager()),
            'layout',
        );

        $handler = $this->getHandler(new HtmlResponse('My widget content'));

        $result = $this->middleware->process($this->createRequest(), $handler);

        $this->assertFalse($this->layout->isLoaded());
        $this->assertSame('My widget content', (string) $result->getBody());
    }

    public function testFactory(): void
    {
        $factory = new LayoutMiddlewareFactory();
        $instance = $factory(Bootstrap::getServiceManager(), LayoutMiddleware::class);

        $this->assertInstanceOf(LayoutMiddleware::class, $instance);
    }

    protected function createRequest(): ServerRequest
    {
        return (new ServerRequest())
            ->withAttribute(LayoutContext::class, $this->context);
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
