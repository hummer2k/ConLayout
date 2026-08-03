# Areas

With areas you can tell the layout updater which layout updates to fetch.
E.g. frontend, backend etc.

Before you can use this feature, you have to tell the layout updater
in which area we are currently in.

````php
<?php
// determine area.
$area = $this->determineArea(); // 'frontend' or 'backend' or 'admin' ...

/* @var $layoutManager \ConLayout\LayoutManager */
$layoutManager = $request->getAttribute(\ConLayout\Http\LayoutContext::class)->getLayoutManager();
$layoutManager->setArea($area);
````

Now you could add the paths for the areas in some application configuration:

````php
<?php
// config/autoload/con-layout.global.php
return [
    'con-layout' => [
        'layout_update_paths' => [
            'frontend' => [
                __DIR__ . '/../layout/frontend'
            ],
            'backend' => [
                __DIR__ . '/../layout/backend'
            ]
        ]
    ]
];
````

### How to determine the current area?

A possible implementation could be a small middleware that determines the
area by the matched route name:

````php
<?php

namespace App\Middleware;

use ConLayout\Updater\LayoutUpdaterInterface;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AreaMiddleware implements MiddlewareInterface
{
    public const AREA_BACKEND = 'backend';

    public function __construct(private LayoutUpdaterInterface $layoutUpdater)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $area = LayoutUpdaterInterface::AREA_DEFAULT;

        $routeResult = $request->getAttribute(RouteResult::class);
        if ($routeResult instanceof RouteResult && $routeResult->isSuccess()) {
            $matchedRouteName = $routeResult->getMatchedRouteName();

            // example for zfc admin
            if (fnmatch('zfcadmin*', $matchedRouteName)) {
                $area = self::AREA_BACKEND;
            }
        }

        $this->layoutUpdater->setArea($area);

        return $handler->handle($request);
    }
}
````
