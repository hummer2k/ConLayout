<?php

declare(strict_types=1);

namespace ConLayout\Middleware;

use ConLayout\Handle\Handle;
use ConLayout\Http\LayoutContext;
use Laminas\Filter\Word\CamelCaseToDash;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use Throwable;

readonly class HandleExtractionMiddleware implements MiddlewareInterface
{
    public const string SEPARATOR = '/';

    public const string ROUTE_NAME_SEPARATOR = '.';

    public const string ERROR_HANDLE_ROUTE_NOT_FOUND = 'error-router-no-match';

    public const int ERROR_PRIORITY = 666;

    private string $errorHandleAttribute;

    public function __construct(
        private CamelCaseToDash $inflector,
        string $errorHandleAttribute = Throwable::class,
    ) {
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
            foreach ($this->getRouteHandles($routeResult) as $handle) {
                $context->addHandle($handle);
            }
        }
    }

    private function getErrorHandleName(mixed $error): string
    {
        if ($error instanceof Throwable) {
            $shortName = (new ReflectionClass($error))->getShortName();
            return 'error-' . $this->inflectName($shortName);
        }
        return (string) $error;
    }

    /**
     * @return Handle[]
     */
    private function getRouteHandles(RouteResult $routeResult): array
    {
        $template = $this->inflectName((string) $routeResult->getMatchedRouteName());

        $priority = 0;
        $actionHandles = [];
        $previousHandle = '';
        $templateParts = explode(self::ROUTE_NAME_SEPARATOR, $template);

        foreach ($templateParts as $name) {
            $priority += 10;
            $actionHandles[] = new Handle($previousHandle . $name, $priority);
            $previousHandle .= $name . self::SEPARATOR;
        }

        return $actionHandles;
    }

    private function inflectName(string $name): string
    {
        $inflected = $this->inflector->filter($name);
        return strtolower(is_array($inflected) ? '' : $inflected);
    }
}
