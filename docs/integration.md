# Integration

## Handler contract

If a handler returns an `HtmlResponse`, ConLayout captures the body of the
response. The body becomes the `content` region of the layout. For advanced
use, return a `ConLayout\Http\ViewModelResponse` that wraps a
`Laminas\View\Model\ViewModel`. ConLayout registers it as the `main.content`
block. If you mark the view model as terminal with `setTerminal(true)`,
ConLayout skips the layout. Then it renders the view model standalone.

````php
<?php
namespace App\Handler;

use ConLayout\Http\ViewModelResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\View\Model\ViewModel;

class PageHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $viewModel = new ViewModel([
            'content' => 'Hello World'
        ]);
        $viewModel->setTemplate('app::page');

        return new ViewModelResponse($viewModel);
    }
}
````

## Access the LayoutManager in handlers

You can get the `LayoutManager` facade with:

- the `ConLayout\Http\LayoutContext` request attribute,
- the `layout()` view helper,
- the `ConLayout\LayoutManager` service.

````php
<?php
namespace App\Handler;

use ConLayout\Handle\Handle;
use ConLayout\Http\LayoutContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PageHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /* @var \ConLayout\LayoutManager $layoutManager */
        $layoutManager = $request->getAttribute(LayoutContext::class)->getLayoutManager();

        // add a custom handle
        $layoutManager->addHandle(new Handle('my-custom-handle', 5));

        // switch the layout area
        $layoutManager->setArea('backend');

        // ...
    }
}
````

In view scripts, use the `layout()` view helper:

````php
<?php
/* @var \ConLayout\LayoutManager $layoutManager */
$layoutManager = $this->layout();

// retrieve a block by ID
$header = $layoutManager->getBlock('header');
````

## Render with the PhpRenderer

ConLayout renders layouts and blocks with the
`Laminas\View\Renderer\PhpRenderer` service. The ConLayout delegator factory
creates the renderer. The factory attaches the `ContainerFilter` to the
renderer filter chain. The factory also attaches the `DebugFilter` when
`con-layout.debug` is enabled.

ConLayout shares the same renderer instance with `mezzio-laminasviewrenderer`.
So ConLayout uses the Mezzio template resolver. The resolver uses the paths
and the map from the `templates` config.

## Error pages

ConLayout has special handles for errors:

- `error-router-no-match` for a request that matches no route.
- `error-<exception-name>` for a request that ends with an exception.

Both handles have the priority `666`. For example, a `NotFoundException`
creates the handle `error-not-found-exception`.

Create layout update files with these names to style your error pages. For
example:

- `layout/<area>/error-router-no-match.xml`
- `layout/<area>/error-not-found-exception.xml`

The `HandleExtractionMiddleware` reads the exception from the request
attribute that the `error_handle_attribute` option defines. The default is
the `Throwable::class` attribute.

## Body class

The `BodyClassMiddleware` adds the matched route name to the body class.
It converts the route name to lowercase. It replaces each character that
is not a letter, a digit, or a dash with a dash. For example, the route
name `app.index.index` becomes `app-index-index`.

Use the `bodyClass` view helper in your layout template to render the
class:

```php
<body class="<?= $this->bodyClass() ?>">
```

You can set a prefix with the `con-layout.body_class_prefix` option.
ConLayout prepends the prefix to the route name.

## Migration from v4 (laminas-mvc)

This version breaks backward compatibility with v4. The following pieces no
longer exist:

- The `ConLayout\Module` class and the ModuleManager integration. Register
  `ConLayout\ConfigProvider` instead.
- The MVC listeners: `ActionHandlesListener`, `BodyClassListener`,
  `LoadLayoutListener`, `PrepareActionViewModelListener`. The middleware of
  the pipeline above replaces them.
- The controller plugin `layoutManager()`. Use the `LayoutContext` request
  attribute, the `layout()` view helper, or the `LayoutManager` service.
- The laminas-developer-tools collector. Mezzio does not have a developer
  toolbar integration.

Block templates and layout templates do not change. The layout update XML
format does not change. The `blocks`, `helpers`, `include`, and `reference`
instructions do not change.
