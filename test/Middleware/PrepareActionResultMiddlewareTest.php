<?php

declare(strict_types=1);

namespace ConLayoutTest\Middleware;

use ConLayout\Http\ViewModelResponse;
use ConLayout\Layout\LayoutInterface;
use ConLayout\Middleware\PrepareActionResultMiddleware;
use ConLayoutTest\AbstractTestCase;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use Laminas\View\Model\ViewModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PrepareActionResultMiddlewareTest extends AbstractTestCase
{
    protected PrepareActionResultMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new PrepareActionResultMiddleware($this->blockPool);
    }

    public function testWithViewModel(): void
    {
        $viewModel = new ViewModel();
        $handler = $this->getHandler(new ViewModelResponse($viewModel));

        $this->middleware->process(new ServerRequest(), $handler);

        $actionResult = $this->blockPool->get(LayoutInterface::BLOCK_ID_ACTION_RESULT);
        $this->assertSame($viewModel, $actionResult);
    }

    public function testDoNothingOnTerminal(): void
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setAppend(false);

        $handler = $this->getHandler(new ViewModelResponse($viewModel));

        $this->middleware->process(new ServerRequest(), $handler);

        $this->assertFalse($viewModel->isAppend());
        $this->assertNull($viewModel->getOption('has_parent'));
        $this->assertFalse($this->blockPool->get(LayoutInterface::BLOCK_ID_ACTION_RESULT));
    }

    public function testCaptureHtmlResponseBody(): void
    {
        $handler = $this->getHandler(new HtmlResponse('My widget content'));

        $this->middleware->process(new ServerRequest(), $handler);

        $actionResult = $this->blockPool->get(LayoutInterface::BLOCK_ID_ACTION_RESULT);
        $this->assertInstanceOf(ViewModel::class, $actionResult);
        $this->assertSame('My widget content', $actionResult->getVariable('content'));
    }

    public function testDoNothingOnNonHtmlResponse(): void
    {
        $handler = $this->getHandler(new JsonResponse(['foo' => 'bar']));

        $this->middleware->process(new ServerRequest(), $handler);

        $this->assertFalse($this->blockPool->get(LayoutInterface::BLOCK_ID_ACTION_RESULT));
    }

    protected function getHandler(ResponseInterface $response): RequestHandlerInterface
    {
        return new class ($response) implements RequestHandlerInterface {
            public function __construct(private ResponseInterface $response)
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };
    }
}
