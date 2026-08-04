# Layout manager

Mezzio does not have a controller plugin. Use the `LayoutManager` facade
instead. You can get the facade with:

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

In view scripts, use the `layout()` view helper to get the facade:

````php
<?php
/* @var $layoutManager \ConLayout\LayoutManager */
$layoutManager = $this->layout();
```

## LayoutManager methods

The `LayoutManager` facade provides these methods:

| Method | Purpose |
| --- | --- |
| `addHandle(HandleInterface $handle)` | Add a handle. |
| `removeHandle(string $handle)` | Remove a handle by name. |
| `getHandles(bool $asObject = false)` | Get all handles, sorted by priority. |
| `setHandles(array $handles)` | Replace all handles. |
| `setArea(string $area)` | Set the layout area. |
| `getArea()` | Get the current area. |
| `getBlock(string $blockId)` | Get a block by id. |
| `addBlock(string $blockId, ModelInterface $block)` | Add a block to the pool. |
| `removeBlock(string $blockId)` | Remove a block from the pool. |
| `getBlocks()` | Get all blocks of the pool. |
| `getRoot()` | Get the root view model. |
| `setRoot(ModelInterface $root)` | Set the root view model. |
| `getLayoutStructure()` | Get the merged layout structure. |
| `load()` | Load the layout. Generate the blocks and inject them into the tree. |
| `generate(array $generators = [])` | Run the generators. |
| `injectBlocks()` | Inject the blocks into the root view model. |

## Worth knowing

- `getBlock()` returns `false` when the block does not exist. Check the
  return value before you use the block.
- The `block()` view helper gives the same access in view scripts. For
  example: `$this->block('header')`.
- Blocks with children that have no id get an anonymous id. The id pattern
  is `anonymous.<capture-to>.<number>`. The capture target of the child
  becomes `<parent-id>::<capture-to>`.
- `LayoutMiddleware` resets the root view model, the handles, and the area
  for each request. Blocks that you add in a handler stay in the block pool
  for the rest of the request.`
