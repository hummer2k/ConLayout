<?php

declare(strict_types=1);

namespace ConLayout\Middleware;

use ConLayout\Handle\Handle;
use ConLayout\Http\LayoutContext;
use ConLayout\Inflector\TemplateInflector;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use Throwable;

class HandleExtractionMiddleware implements MiddlewareInterface
{
    public const SEPARATOR = '/';

    public const ERROR_HANDLE_ROUTE_NOT_FOUND = 'error-router-no-match';

    public const ERROR_PRIORITY = 666;

    private TemplateInflector $inflector;

    private string $errorHandleAttribute;

    public function __construct(
        TemplateInflector $inflector,
        string $errorHandleAttribute = Throwable::class
    ) {
        $this->inflector = $inflector;
        $this->errorHandleAttribute = $errorHandleAttribute;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $context = $request->getAttribute(LayoutContext::class);

        if ($context instanceof LayoutContext) {
            $this->injectHandles($context, $request);
        }

        return $handler->handle($request);
    }

    private function injectHandles(LayoutContext $context, ServerRequestInterface $request): void
    {
        $routeResult = $request->getAttribute(RouteResult::class);

        if ($routeResult instanceof RouteResult && $routeResult->isFailure()) {
            $context->addHandle(new Handle(self::ERROR_HANDLE_ROUTE_NOT_FOUND, self::ERROR_PRIORITY));
            return;
        }

        if (null !== ($error = $request->getAttribute($this->errorHandleAttribute))) {
            $context->addHandle(new Handle($this->getErrorHandleName($error), self::ERROR_PRIORITY));
            return;
        }

        if ($routeResult instanceof RouteResult && $routeResult->isSuccess()) {
            foreach ($this->getActionHandles($routeResult) as $handle) {
                $context->addHandle($handle);
            }
        }
    }

    private function getErrorHandleName(mixed $error): string
    {
        if ($error instanceof Throwable) {
            $shortName = (new ReflectionClass($error))->getShortName();
            return 'error-' . $this->inflector->inflectName($shortName);
        }
        return (string) $error;
    }

    /**
     * @return Handle[]
     */
    private function getActionHandles(RouteResult $routeResult): array
    {
        $params = $routeResult->getMatchedParams();
        $controller = $params['controller'] ?? '';
        $routeName = (string) $routeResult->getMatchedRouteName();

        if (is_string($controller) && $controller) {
            $template = $this->inflector->mapController($controller);
        } else {
            $template = $this->inflector->inflectName($routeName);
        }

        $action = $params['action'] ?? null;
        if (is_string($action) && null !== $action) {
            $template .= self::SEPARATOR . $this->inflector->inflectName($action);
        }

        $priority = 0;
        $actionHandles = [];
        $previousHandle = '';
        $templateParts = explode(self::SEPARATOR, $template);

        foreach ($templateParts as $name) {
            $priority += 10;
            $actionHandles[] = new Handle($previousHandle . $name, $priority);
            $previousHandle .= $name . self::SEPARATOR;
        }

        return $actionHandles;
    }
}
