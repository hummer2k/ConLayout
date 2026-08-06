<?php

declare(strict_types=1);

namespace ConLayout\Middleware;

use ConLayout\Http\LayoutContext;
use ConLayout\LayoutManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class LayoutContextMiddleware implements MiddlewareInterface
{
    public function __construct(private LayoutManager $layoutManager) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle(
            $request->withAttribute(LayoutContext::class, new LayoutContext($this->layoutManager)),
        );
    }
}
