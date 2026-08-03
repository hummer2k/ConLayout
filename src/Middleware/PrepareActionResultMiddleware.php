<?php

declare(strict_types=1);

namespace ConLayout\Middleware;

use ConLayout\Block\BlockPoolInterface;
use ConLayout\Http\ViewModelResponse;
use ConLayout\Layout\LayoutInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\View\Model\ViewModel;

class PrepareActionResultMiddleware implements MiddlewareInterface
{
    public const CONTENT_TEMPLATE = 'con-layout::content';

    private BlockPoolInterface $blockPool;

    public function __construct(BlockPoolInterface $blockPool)
    {
        $this->blockPool = $blockPool;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if ($response instanceof ViewModelResponse) {
            $viewModel = $response->getViewModel();
            if ($viewModel->terminate()) {
                $viewModel->setOption('has_parent', null);
            } else {
                $this->blockPool->add(LayoutInterface::BLOCK_ID_ACTION_RESULT, $viewModel);
            }
            return $response;
        }

        if ($this->isHtmlResponse($response)) {
            $content = new ViewModel();
            $content->setTemplate(self::CONTENT_TEMPLATE);
            $content->setVariable('content', (string) $response->getBody());
            $this->blockPool->add(LayoutInterface::BLOCK_ID_ACTION_RESULT, $content);
        }

        return $response;
    }

    private function isHtmlResponse(ResponseInterface $response): bool
    {
        $contentType = $response->getHeaderLine('Content-Type');
        return '' === $contentType || str_contains(strtolower($contentType), 'text/html');
    }
}
