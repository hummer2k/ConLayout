# Areas

An area tells the layout updater which layout updates to fetch. For example:
`frontend`, `backend`, `admin`, and so on.

Before you use this feature, you must tell the layout updater the current
area.

````php
<?php
// determine area.
$area = $this->determineArea(); // 'frontend' or 'backend' or 'admin' ...

/* @var $layoutManager \ConLayout\LayoutManager */
$layoutManager = $request->getAttribute(\ConLayout\Http\LayoutContext::class)->getLayoutManager();
$layoutManager->setArea($area);
````

Then add the paths for the areas in the application configuration:

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

### Determine the current area

You can determine the area with a small middleware. The middleware uses the
matched route name:

```php
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
```

## How ConLayout finds the layout update files

The layout updater reads one file per handle. The file name is the handle
name plus a file extension. For example, the handle `home` reads the file
`home.xml`.

ConLayout searches the file in the paths of the `global` area first. Then
it searches in the paths of the current area. It merges the files of both
areas. The `global` area is always searched, whatever the current area is.

## The default area

The default area is `frontend`. You can change it with the
`con-layout.default_area` option:

```php
return [
    'con-layout' => [
        'default_area' => 'admin',
    ],
];
```

Note: The `LayoutMiddleware` sets the area from the `default_area` option
before it loads the layout. This overwrites an area that you set earlier in
the request. Set `default_area` in the configuration when you need one
fixed area for the whole application.

## Check the current area

The debug toolbar shows the current area in the Structure tab. Use the
toolbar to verify the area that ConLayout uses for the request. For more
information, see `docs/toolbar.md`.
