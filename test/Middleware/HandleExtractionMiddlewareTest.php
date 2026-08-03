<?php

declare(strict_types=1);

namespace ConLayoutTest\Middleware;

use ConLayout\Http\LayoutContext;
use ConLayout\Inflector\TemplateInflector;
use ConLayout\LayoutManager;
use ConLayout\Middleware\HandleExtractionMiddleware;
use ConLayoutTest\AbstractTestCase;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HandleExtractionMiddlewareTest extends AbstractTestCase
{
    protected HandleExtractionMiddleware $middleware;

    protected LayoutContext $context;

    protected function setUp(): void
    {
        parent::setUp();
        $layoutManager = new LayoutManager($this->layout, $this->layoutUpdater, $this->blockPool);
        $this->context = new LayoutContext($layoutManager);
        $this->middleware = new HandleExtractionMiddleware(new TemplateInflector());
    }

    public function testApplicationIndexIndex(): void
    {
        $request = $this->createRequest([
            'controller' => 'Application\Controller\Index',
            'action' => 'index'
        ]);

        $this->middleware->process($request, $this->getHandler());

        $handles = implode(' ', $this->getHandleNames());

        $expectedHandles = [
            'application',
            'application/index',
            'application/index/index'
        ];

        foreach ($expectedHandles as $expectedHandle) {
            $this->assertStringContainsString($expectedHandle, $handles);
        }
    }

    public function testApplicationIndexIndexWithControllerMap(): void
    {
        $inflector = new TemplateInflector();
        $inflector->setControllerMap([
            'Application' => 'app'
        ]);

        $this->middleware = new HandleExtractionMiddleware($inflector);

        $request = $this->createRequest([
            'controller' => 'Application\Controller\Index',
            'action' => 'index'
        ]);

        $this->middleware->process($request, $this->getHandler());

        $handles = implode(' ', $this->getHandleNames());

        $expectedHandles = [
            'app',
            'app/index',
            'app/index/index'
        ];

        foreach ($expectedHandles as $expectedHandle) {
            $this->assertStringContainsString($expectedHandle, $handles);
        }
    }

    public function testRouteNameBasedHandles(): void
    {
        $request = $this->createRequest([], 'blog/post');

        $this->middleware->process($request, $this->getHandler());

        $handles = implode(' ', $this->getHandleNames());

        $this->assertStringContainsString('blog', $handles);
        $this->assertStringContainsString('blog/post', $handles);
    }

    public function testInjectErrorHandleFromString(): void
    {
        $this->middleware = new HandleExtractionMiddleware(new TemplateInflector(), 'error');

        $request = $this->createRequest([], 'test')
            ->withAttribute('error', 'test-error');

        $this->middleware->process($request, $this->getHandler());

        $this->assertStringContainsString('test-error', implode(' ', $this->getHandleNames()));
    }

    public function testInjectErrorHandleFromThrowable(): void
    {
        $request = $this->createRequest([], 'test')
            ->withAttribute(HandleExtractionMiddleware::class, new \RuntimeException('something went wrong'));

        $this->middleware = new HandleExtractionMiddleware(new TemplateInflector(), HandleExtractionMiddleware::class);

        $this->middleware->process($request, $this->getHandler());

        $this->assertStringContainsString('error-runtime-exception', implode(' ', $this->getHandleNames()));
    }

    public function testInjectErrorHandleOnRouteFailure(): void
    {
        $request = (new ServerRequest())
            ->withAttribute(LayoutContext::class, $this->context)
            ->withAttribute(RouteResult::class, RouteResult::fromRouteFailure([]));

        $this->middleware->process($request, $this->getHandler());

        $this->assertStringContainsString(
            HandleExtractionMiddleware::ERROR_HANDLE_ROUTE_NOT_FOUND,
            implode(' ', $this->getHandleNames())
        );
    }

    protected function createRequest(array $params, string $routeName = 'test'): ServerRequest
    {
        $middleware = new class implements MiddlewareInterface {
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return new Response();
            }
        };
        $route = new Route('/test', $middleware, ['GET'], $routeName);

        return (new ServerRequest())
            ->withAttribute(LayoutContext::class, $this->context)
            ->withAttribute(RouteResult::class, RouteResult::fromRoute($route, $params));
    }

    protected function getHandler(): RequestHandlerInterface
    {
        return new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };
    }

    protected function getHandleNames(): array
    {
        $handleNames = [];
        foreach ($this->context->getHandles() as $handle) {
            $handleNames[] = $handle->getName();
        }
        return $handleNames;
    }
}
