# Layout manager

In Mezzio the controller plugin no longer exists. Use the `LayoutManager`
facade instead. It is available via:

- the `ConLayout\Http\LayoutContext` request attribute (set by the
  `LayoutContextMiddleware`),
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
use Laminas\View\Model\ViewModel;

class IndexHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /* @var $layoutManager \ConLayout\LayoutManager */
        $layoutManager = $request->getAttribute(LayoutContext::class)->getLayoutManager();

        // add a custom handle
        $layoutManager->addHandle(new Handle('my-custom-handle', 5));

        // remove a handle
        $layoutManager->removeHandle('my-custom-handle');

        // retrieve a block by ID
        $header = $layoutManager->getBlock('header');

        // add a block programatically
        $block = new ViewModel();
        $block->setOption('parent', 'sidebar');
        $block->setOption('order', 10);

        $layoutManager->addBlock('my.block', $block);

        // ...
    }
}
````

Inside view scripts the same facade is available through the `layout()`
view helper:

````php
<?php
/* @var $layoutManager \ConLayout\LayoutManager */
$layoutManager = $this->layout();
````
