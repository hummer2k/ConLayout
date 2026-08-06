<?php

declare(strict_types=1);

namespace ConLayout\Middleware;

use ConLayout\View\Helper\BodyClass;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BodyClassMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly BodyClass $bodyClassHelper,
        private readonly string $prefix = '',
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeResult = $request->getAttribute(RouteResult::class);
        if ($routeResult instanceof RouteResult && $routeResult->isSuccess()) {
            $routeName = (string) $routeResult->getMatchedRouteName();
            $className = $this->prefix . preg_replace('#[^a-z0-9-]+#i', '-', $routeName);
            ($this->bodyClassHelper)(strtolower((string) $className));
        }
        return $handler->handle($request);
    }
}
