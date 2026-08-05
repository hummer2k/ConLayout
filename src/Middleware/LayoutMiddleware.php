<?php

declare(strict_types=1);

namespace ConLayout\Middleware;

use ConLayout\Http\LayoutContext;
use ConLayout\Http\ViewModelResponse;
use ConLayout\LayoutManager;
use ConLayout\Options\ModuleOptions;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\View\Exception\DomainException;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LayoutMiddleware implements MiddlewareInterface
{
    private LayoutManager $layoutManager;

    private PhpRenderer $renderer;

    private ModuleOptions $moduleOptions;

    private HelperPluginManager $helperPluginManager;

    private string $layoutTemplate;

    public function __construct(
        LayoutManager $layoutManager,
        PhpRenderer $renderer,
        ModuleOptions $moduleOptions,
        HelperPluginManager $helperPluginManager,
        string $layoutTemplate = '',
    ) {
        $this->layoutManager = $layoutManager;
        $this->renderer = $renderer;
        $this->moduleOptions = $moduleOptions;
        $this->helperPluginManager = $helperPluginManager;
        $this->layoutTemplate = $layoutTemplate;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->helperPluginManager->resetState();
        $response = $handler->handle($request);

        if (!$this->moduleOptions->isEnabled()) {
            return $response;
        }

        if ($response instanceof ViewModelResponse) {
            $viewModel = $response->getViewModel();
            if ($viewModel->terminate() || !$this->layoutTemplate) {
                return new HtmlResponse(
                    $this->renderer->render($viewModel),
                    $response->getStatusCode(),
                    $response->getHeaders(),
                );
            }
        } elseif (!$this->layoutTemplate || !$this->isHtmlResponse($response)) {
            return $response;
        }

        $context = $request->getAttribute(LayoutContext::class);
        $layoutManager = $context instanceof LayoutContext
            ? $context->getLayoutManager()
            : $this->layoutManager;

        $layoutManager->setArea($this->moduleOptions->getDefaultArea());
        if ($context instanceof LayoutContext) {
            $layoutManager->setHandles($context->getHandles());
        }

        $root = new ViewModel();
        $root->setTemplate($this->layoutTemplate);
        $layoutManager->setRoot($root);
        $layoutManager->load();

        return new HtmlResponse(
            $this->renderTree($root),
            $response->getStatusCode(),
            $response->getHeaders(),
        );
    }

    private function renderTree(ModelInterface $model, ?ModelInterface $root = null): string
    {
        $root ??= $model;

        foreach ($model as $child) {
            if ($child->terminate()) {
                throw new DomainException('Inconsistent state; child view model is marked as terminal');
            }

            $capture = $child->captureTo();
            if ('' === $capture) {
                continue;
            }

            $result = $this->renderTree($child, $root);

            if ($child->isAppend()) {
                $oldResult = $model->{$capture};
                $model->setVariable($capture, $oldResult . $result);
                continue;
            }

            $model->setVariable($capture, $result);
        }

        return $this->renderer->render($model);
    }

    private function isHtmlResponse(ResponseInterface $response): bool
    {
        $contentType = $response->getHeaderLine('Content-Type');
        return '' === $contentType || str_contains(strtolower($contentType), 'text/html');
    }
}
