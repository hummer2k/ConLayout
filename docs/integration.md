# Integration

## Handler contract

Any PSR-15 handler that returns an `HtmlResponse` gets its body captured as
the `content` region of the layout. For advanced use, return a
`ConLayout\Http\ViewModelResponse` wrapping a `Laminas\View\Model\ViewModel`;
it will be registered as the `action.result` block. If the view model is
marked as terminal (`setTerminal(true)`), the layout is skipped and the view
model is rendered standalone.

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

## Accessing the LayoutManager in handlers

The `LayoutManager` facade is exposed via:

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

In view scripts you can use the `layout()` view helper:

````php
<?php
/* @var \ConLayout\LayoutManager $layoutManager */
$layoutManager = $this->layout();

// retrieve a block by ID
$header = $layoutManager->getBlock('header');
````

## Rendering with the PhpRenderer

Layouts and blocks are rendered with the `Laminas\View\Renderer\PhpRenderer`
service. The renderer is created by the ConLayout delegator factory, which
attaches the `ContainerFilter` (and the `DebugFilter` when
`con-layout.debug` is enabled) to the renderer filter chain.

The same renderer instance is shared with `mezzio-laminasviewrenderer`, so the
Mezzio template resolver (paths and map from the `templates` config) is used.

## Migration from v4 (laminas-mvc)

This is a major BC break. The following pieces no longer exist:

- the `ConLayout\Module` class and the ModuleManager integration; register
  `ConLayout\ConfigProvider` instead,
- the MVC listeners (`ActionHandlesListener`, `BodyClassListener`,
  `LoadLayoutListener`, `PrepareActionViewModelListener`); they are replaced
  by the middleware of the pipeline above,
- the controller plugin `layoutManager()`; use the `LayoutContext` request
  attribute, the `layout()` view helper or the `LayoutManager` service,
- the laminas-developer-tools collector; Mezzio has no developer toolbar
  integration.

Block and layout templates stay unchanged, as does the layout update XML
format and the `blocks`, `helpers`, `include` and `reference` instructions.
