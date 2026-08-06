<?php

declare(strict_types=1);

namespace ConLayoutTest\Middleware;

use ConLayout\Http\LayoutContext;
use ConLayout\LayoutManager;
use ConLayout\Middleware\HandleExtractionMiddleware;
use ConLayoutTest\AbstractTestCase;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Laminas\Filter\Word\CamelCaseToDash;
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
        $layoutManager = new LayoutManager($this->layout, $this->layoutUpdater, $this->blockRegistry);
        $this->context = new LayoutContext($layoutManager);
        $this->middleware = new HandleExtractionMiddleware(new CamelCaseToDash());
    }

    public function testRouteNameBasedHandles(): void
    {
        $request = $this->createRequest([], 'blog.post');

        $this->middleware->process($request, $this->getHandler());

        $handles = implode(' ', $this->getHandleNames());

        $this->assertStringContainsString('blog', $handles);
        $this->assertStringContainsString('blog/post', $handles);
    }

    public function testPrefixedRouteNameBasedHandles(): void
    {
        $request = $this->createRequest([], 'app.home');

        $this->middleware->process($request, $this->getHandler());

        $handles = [];
        foreach ($this->context->getHandles() as $handle) {
            $handles[$handle->getName()] = $handle->getPriority();
        }

        $this->assertSame(10, $handles['app']);
        $this->assertSame(20, $handles['app/home']);
    }

    public function testMultiSegmentRouteNameBasedHandles(): void
    {
        $request = $this->createRequest([], 'customer.account.dashboard');

        $this->middleware->process($request, $this->getHandler());

        $handles = [];
        foreach ($this->context->getHandles() as $handle) {
            $handles[$handle->getName()] = $handle->getPriority();
        }

        $this->assertSame(10, $handles['customer']);
        $this->assertSame(20, $handles['customer/account']);
        $this->assertSame(30, $handles['customer/account/dashboard']);
    }

    public function testCamelCaseRouteNameBasedHandles(): void
    {
        $request = $this->createRequest([], 'app.homePage');

        $this->middleware->process($request, $this->getHandler());

        $handles = [];
        foreach ($this->context->getHandles() as $handle) {
            $handles[$handle->getName()] = $handle->getPriority();
        }

        $this->assertSame(10, $handles['app']);
        $this->assertSame(20, $handles['app/home-page']);
    }

    public function testInjectErrorHandleFromString(): void
    {
        $this->middleware = new HandleExtractionMiddleware(new CamelCaseToDash(), 'error');

        $request = $this->createRequest([], 'test')
            ->withAttribute('error', 'test-error');

        $this->middleware->process($request, $this->getHandler());

        $this->assertStringContainsString('test-error', implode(' ', $this->getHandleNames()));
    }

    public function testInjectErrorHandleFromThrowable(): void
    {
        $request = $this->createRequest([], 'test')
            ->withAttribute(HandleExtractionMiddleware::class, new \RuntimeException('something went wrong'));

        $this->middleware = new HandleExtractionMiddleware(new CamelCaseToDash(), HandleExtractionMiddleware::class);

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
            implode(' ', $this->getHandleNames()),
        );
    }

    protected function createRequest(array $params, string $routeName = 'test'): ServerRequest
    {
        $middleware = new class implements MiddlewareInterface {
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler,
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
