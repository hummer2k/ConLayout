<?php

declare(strict_types=1);

namespace ConLayout\Toolbar;

use Laminas\Diactoros\Stream;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function microtime;
use function memory_get_peak_usage;

class ToolbarMiddleware implements MiddlewareInterface
{
    public const DEFAULT_TEMPLATE = 'con-layout::toolbar';

    private ToolbarData $toolbarData;

    private PhpRenderer $renderer;

    private bool $enabled;

    private string $template;

    public function __construct(
        ToolbarData $toolbarData,
        PhpRenderer $renderer,
        bool $enabled = false,
        string $template = self::DEFAULT_TEMPLATE,
    ) {
        $this->toolbarData = $toolbarData;
        $this->renderer = $renderer;
        $this->enabled = $enabled;
        $this->template = $template;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $startTime = microtime(true);
        $response = $handler->handle($request);
        $duration = microtime(true) - $startTime;

        if (!$this->enabled || !$this->isHtmlResponse($response)) {
            return $response;
        }

        if (!$this->toolbarData->hasLayout()) {
            return $response;
        }

        $toolbar = $this->renderer->render($this->template, [
            'handles'        => $this->toolbarData->getHandles(),
            'updateFiles'    => $this->toolbarData->getUpdateFiles(),
            'layoutTemplate' => $this->toolbarData->getLayoutTemplate(),
            'area'           => $this->toolbarData->getArea(),
            'blocks'         => $this->toolbarData->getBlocks(),
            'unrendered'     => $this->toolbarData->getUnrenderedBlocks(),
            'structure'      => $this->toolbarData->getStructureSummary(),
            'helpers'        => $this->toolbarData->getHelpers(),
            'options'        => $this->toolbarData->getModuleOptions(),
            'duration'       => round($duration * 1000, 2),
            'memory'         => memory_get_peak_usage(true),
        ]);

        $body = (string) $response->getBody();
        $position = stripos($body, '</body>');
        if (false !== $position) {
            $body = substr($body, 0, $position) . $toolbar . substr($body, $position);
        } else {
            $body .= $toolbar;
        }

        $stream = new Stream('php://temp', 'wb+');
        $stream->write($body);
        return $response->withBody($stream);
    }

    private function isHtmlResponse(ResponseInterface $response): bool
    {
        $contentType = $response->getHeaderLine('Content-Type');
        return '' === $contentType || str_contains(strtolower($contentType), 'text/html');
    }
}
