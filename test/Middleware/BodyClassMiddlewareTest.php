<?php

declare(strict_types=1);

namespace ConLayoutTest\Middleware;

use ConLayout\Middleware\BodyClassMiddleware;
use ConLayout\View\Helper\BodyClass;
use ConLayoutTest\AbstractTestCase;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BodyClassMiddlewareTest extends AbstractTestCase
{
    public function testAddBodyClass(): void
    {
        $bodyClassHelper = new BodyClass();
        $middleware = new BodyClassMiddleware($bodyClassHelper);

        $middleware->process(
            $this->createRequest('my/SOME/route'),
            $this->getHandler()
        );

        $this->assertEquals('my-some-route', (string) $bodyClassHelper);
    }

    public function testAddBodyClassWithPrefix(): void
    {
        $bodyClassHelper = new BodyClass();
        $middleware = new BodyClassMiddleware($bodyClassHelper, 'layout-');

        $middleware->process(
            $this->createRequest('home'),
            $this->getHandler()
        );

        $this->assertEquals('layout-home', (string) $bodyClassHelper);
    }

    public function testDoNothingWithoutRouteResult(): void
    {
        $bodyClassHelper = new BodyClass();
        $middleware = new BodyClassMiddleware($bodyClassHelper);

        $middleware->process(
            new ServerRequest(),
            $this->getHandler()
        );

        $this->assertEquals('', (string) $bodyClassHelper);
    }

    protected function createRequest(string $routeName): ServerRequest
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
            ->withAttribute(RouteResult::class, RouteResult::fromRoute($route, []));
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
}
