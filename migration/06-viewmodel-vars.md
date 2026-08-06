# Migration: plain ViewModel block variables, drop block class layer

## Scope & baseline

- `05-laminas-view-v3.md` is applied: laminas-view v3, `mezzio-laminasviewrenderer` v3, servicemanager v4. Block metadata is stored as one `stdClass` under the reserved variable key `ConLayout\Block\AbstractBlock::METADATA_KEY = '__conlayout'`, and `AbstractBlock` composes an internal `ViewModel` instead of extending it.
- The result is not satisfactory. Two simplifications:
  1. **Metadata becomes single variables, without the `__conlayout` prefix.** Each metadata item (`block_id`, `parent`, `order`, `before`, `after`, `container`, `wrapper`, `has_parent`, `parent_block`, `tag`, `html_*`, plus arbitrary layout-XML `options` keys) is stored as its own variable directly on the model. `$block->getVariable('order')` / `$block->setVariable('order', 10)` instead of `$block->getVariable('__conlayout')->order`. The `stdClass` bag, the `__conlayout` key and `METADATA_KEY` go away.
  2. **The block class layer is dropped.** `BlockInterface`, `AbstractBlock` and the block implementations that extended the `ViewModel` (`Container`, `ContainerFactory`, `view/container.phtml`, the `blocks.container` service, the `con-layout::container` template map entry) are removed. Blocks are plain `Laminas\View\Model\ViewModel` instances. The only interfaces used are the Laminas interfaces that `ViewModel` itself implements (`ModelInterface`, `ClearableModelInterface`, `Countable`, `IteratorAggregate`).
- `BlockInterface` also declared `setView`/`getView` (`RendererInterface`), consumed only by `BlockFactory::configure()` to inject the `PhpRenderer`. Dropping the interface removes that wiring; `BlockFactory`'s `$container` constructor parameter existed solely for that call and is removed too.
- `ContainerFilter` rework is intentionally deferred (see Risks): only the minimal mechanical metadata conversion is applied there, no reserved-key strip list design.
- The three classes `src/Block/AbstractBlock.php`, `src/Block/BlockInterface.php`, `src/Block/Container.php` are already deleted (staged). All remaining `AbstractBlock::METADATA_KEY` references in `src/` and `test/` are dangling and must be converted by this migration.

## Decisions

1. **Each metadata item is its own variable, no prefix, no bag.** Layout-XML `options` are written by `BlockFactory::configure()` directly with `$block->setVariable($name, $option)`. `block_id` is written by `BlockPool::add()` and `BlockFactory::createBlock()`. Read sites use `$block->getVariable($name)`.
2. **`block_id` presence marks a conlayout block.** `ContainerFilter` and `DebugFilter` guard on `getVariable('block_id')` instead of the removed `__conlayout` bag presence. Every block enters the pool through `BlockPool::add()`, which writes `block_id`, so all pooled blocks carry the marker; plain models rendered outside the pool (controller `ViewModelResponse` terminate case, nested string-template renders) do not.
3. **Blocks are plain `Laminas\View\Model\ViewModel` instances.** `BlockManager::validate()` accepts `ModelInterface` only. Custom block classes from layout-XML `class` entries must be instantiable without constructor arguments and implement `ModelInterface`; consumers can no longer rely on `setView`/`getView` or `instanceof BlockInterface`/`AbstractBlock`.
4. **`BlockFactory` constructor drops the `$container` parameter.** It was only used for the removed `BlockInterface::setView()` call. `BlockFactoryFactory` and test wiring are updated accordingly. This is a major-version break, consistent with the rest of the v3 migration.
5. **`ContainerFilter` keeps its structure, minimal conversion only.** Guard becomes `getVariable('block_id') === null`; the container option is read via `getVariable('container')`; the nested-render `unset` keeps inline literal keys. The re-entrancy guard design (reserved-key strip list) is out of scope and tracked in Risks.
6. **`tag`/`html_*` options become inert variables.** They are still stored through the generic options loop, but no consumer renders them once `Container` is gone. Kept as plain variables for consumer block code that reads them via `getVariable('tag')`.

## Implementation

### `src/Block/BlockPool.php`

- `add()`: replace `$this->writeMetadata($block, 'block_id', $blockId)` with `$block->setVariable('block_id', $blockId);`.
- `sort()`: replace `readMetadata(...)`/`writeMetadata(...)` calls with direct variable access:
  - `if ($beforeBlockId = $block->getVariable('before'))` / `$block->setVariable('order', $beforeBlock->getVariable('order', 0) - 1)`.
  - `if ($afterBlockId = $block->getVariable('after'))` / `$block->setVariable('order', $afterBlock->getVariable('order', 0) + 1)`.
  - `$orderA = $a->getVariable('order', 0)` / `$orderB = $b->getVariable('order', 0)`.
- `determineAnonymousBlockId()`: `$block->getVariable('block_id')`, then `$block->setVariable('block_id', $blockId)`.
- Delete the private `readMetadata`/`writeMetadata` helpers and the `use stdClass;` import.
- Docblock `@var ModelInterface[]|BlockInterface[]` on `$blocks` becomes `@var ModelInterface[]`.

### `src/Block/Factory/BlockFactory.php`

- `createBlock()`: replace the `stdClass` bag initialization with `$block->setVariable('block_id', $blockId);`.
- `configure()`: replace the options loop
  ```php
  $meta = $block->getVariable(AbstractBlock::METADATA_KEY) ?? new stdClass();
  foreach ($this->getOption('options', $specs) as $name => $option) {
      $meta->{$name} = $option;
  }
  $block->setVariable(AbstractBlock::METADATA_KEY, $meta);
  ```
  with
  ```php
  foreach ($this->getOption('options', $specs) as $name => $option) {
      $block->setVariable($name, $option);
  }
  ```
- Remove the block
  ```php
  if ($block instanceof BlockInterface) {
      $block->setView($this->container->get(PhpRenderer::class));
  }
  ```
- Remove the `?ContainerInterface $container = null` constructor parameter and `$this->container` assignment.
- Drop imports: `use ConLayout\Block\AbstractBlock;`, `use ConLayout\Block\BlockInterface;`, `use Laminas\View\Renderer\PhpRenderer;`, `use stdClass;`.
- Keep the `$block->setVariable('block', $block);` self-reference and the `toBool()` helper added by the v3 migration.

### `src/Block/Factory/BlockFactoryFactory.php`

- Drop the `$container` argument from the `new BlockFactory(...)` call.

### `src/Layout/Layout.php`

- `injectBlocks()`: replace
  ```php
  $meta = $block->getVariable(AbstractBlock::METADATA_KEY) ?? new stdClass();
  $meta->parent_block = $parentBlock;
  $block->setVariable(AbstractBlock::METADATA_KEY, $meta);
  ```
  with `$block->setVariable('parent_block', $parentBlock);`.
- `getCaptureTo()`: `if ($parent = $block->getVariable('parent') ?? null)` becomes `if ($parent = $block->getVariable('parent'))` (`getVariable` already returns the default on unset).
- Drop the `use ConLayout\Block\AbstractBlock;` and `use stdClass;` imports.

### `src/Generator/BlocksGenerator.php`

- Replace the bag block:
  ```php
  $meta = $block->getVariable(AbstractBlock::METADATA_KEY) ?? new stdClass();
  $meta->has_parent = true;
  if (!($meta->parent ?? null)) {
      $meta->parent = $parentId;
  }
  $block->setVariable(AbstractBlock::METADATA_KEY, $meta);
  ```
  with:
  ```php
  $block->setVariable('has_parent', true);
  if (!$block->getVariable('parent')) {
      $block->setVariable('parent', $parentId);
  }
  ```
- Drop the `use ConLayout\Block\AbstractBlock;` and `use stdClass;` imports.

### `src/Middleware/PrepareMainContentMiddleware.php`

- Replace the bag block with `$viewModel->setVariable('has_parent', null);` (keeps the existing semantics; the value is a marker, nothing reads it).
- Drop the `use ConLayout\Block\AbstractBlock;` and `use stdClass;` imports.

### `src/Toolbar/ToolbarData.php`

- `collect()` / `getBlockData()`: `$model->getVariable('block_id')`.
- `readMetadata(ModelInterface $model, string $name): mixed`: implement as `$model->getVariable($name)` followed by the existing `null`/`''` to `null` normalization.
- Drop the `use ConLayout\Block\AbstractBlock;` import.

### `src/Filter/ContainerFilter.php` (minimal only)

- Guard: `if ($viewModel->getVariable('block_id') === null) { return $value; }` (replaces the `$meta === null` check).
- `$container = trim((string) ($viewModel->getVariable('container') ?? ''));`.
- Nested-render `unset($values[AbstractBlock::METADATA_KEY]);` becomes inline literal keys, e.g. `unset($values['block_id'], $values['container'], $values['wrapper'], $values['parent'], $values['order'], $values['before'], $values['after'], $values['has_parent'], $values['parent_block'], $values['tag']);`.
- Drop the `use ConLayout\Block\AbstractBlock;` import.
- No reserved-key constant, no re-entrancy design work in this migration.

### `src/Filter/DebugFilter.php`

- `$blockId = $viewModel?->getVariable('block_id');`.
- Drop the `use ConLayout\Block\AbstractBlock;` import.

### `src/BlockManager.php`

- `validate()`: accept `ModelInterface` only; drop the `BlockInterface` branch and the import; update the exception message to reference `Laminas\View\Model\ModelInterface` only.

### `src/LayoutManager.php`

- Drop the unused `use ConLayout\Block\BlockInterface;` import.

### `src/ConfigProvider.php`

- Remove `use ConLayout\Block\Container;` (and the `ContainerFactory` reference if the import is present).
- `getBlockConfig()`: return `[]` (drop the `factories`/`shared` entries for `container`).
- `getTemplates()`: remove the `'con-layout::container'` template map entry.

### Deletions

- `view/container.phtml`.
- `test/Block/ContainerTest.php`.
- `test/Block/AbstractBlockTest.php` (delete if present; currently not on disk).
- Already deleted: `src/Block/AbstractBlock.php`, `src/Block/BlockInterface.php`, `src/Block/Container.php`, `src/Block/ContainerFactory.php` (was untracked).

## Tests

- `test/BlockManagerTest.php`: remove the `TestBlock extends AbstractBlock` fixture and its validation call; validate a second plain `ViewModel` instead.
- `test/Block/Factory/BlockFactoryTest.php`:
  - Fixture classes `TplBlock`, `MyBlock`, `BlockImpl` (currently `extends AbstractBlock`) become plain `ViewModel` instances used inline.
  - Drop `testCreateBlockImpl()` (asserted `getView()`).
  - Drop the `PhpRenderer` service wiring in `setUp()` (no longer needed once the setView path is gone).
  - `testCreateBlock()`: assert `$block->getVariable('option1')`, `$block->getVariable('order')` instead of the bag.
  - `testClass()`: `$block->getVariable('block_id')`.
- `test/Block/BlockPoolTest.php`: `createBlock(array $metadata)` helper writes each key with `setVariable($key, $value)`; anonymous-id assertion becomes `$child->getVariable('block_id')`.
- `test/Layout/LayoutTest.php`: `setVariable('parent', 'some.parent')`, `setVariable('block_id', ...)`; `parent_block` assertions become `$childBlock->getVariable('parent_block')`.
- `test/Generator/BlocksGeneratorTest.php`: assert `getVariable('has_parent')` / `getVariable('parent')`.
- `test/Toolbar/ToolbarDataTest.php`, `test/Toolbar/ToolbarMiddlewareTest.php`: write `block_id` (and any `order`/`parent`/`container`/`wrapper` values) as direct variables.
- `test/Middleware/PrepareMainContentMiddlewareTest.php`: `$this->assertNull($viewModel->getVariable('has_parent'));`.
- `test/Filter/DebugFilterTest.php`: `setVariable('block_id', ...)`.
- `test/Filter/ContainerFilterTest.php`: `setVariable('block_id', ...)` plus `setVariable('container', ...)`.
- `test/ConfigProviderTest.php`: replace both `(object) [...]` bags with `setVariable('block_id', ...)` / `setVariable('container', ...)`.

## Docs

- `docs/layout-instructions.md`:
  - Class comments (block examples, currently "Can extend from ConLayout\Block\AbstractBlock / Should implement ConLayout\Block\BlockInterface"): reword to plain `Laminas\View\Model\ViewModel` (default) or any class implementing `Laminas\View\Model\ModelInterface`.
  - "Internal block metadata" section: document single-variable storage, the reserved variable names (`block_id`, `parent`, `order`, `before`, `after`, `container`, `wrapper`, `has_parent`, `parent_block`, `tag`, `html_*`), and the new read/write pattern, e.g. `$block->setVariable('order', 10);`.
- `docs/controller-plugin.md`: update the programmatic block example to `$block->setVariable('parent', 'sidebar');` / `$block->setVariable('order', 10);` and drop the `AbstractBlock::METADATA_KEY` reference.

## Verification

- From `vendor/hummer2k/conlayout/`: `../../../vendor/bin/phpunit -c phpunit.xml.dist` (whole suite green).
- `composer cs-check` / `composer cs-fix` (php-cs-fixer, PER Coding Style).
- App: `composer test` in the mezzio app, then a browser/toolbar check (ddev) exercising a page with a `container`-option block and the debug toolbar (block-id HTML comments).

## Risks & open points

- **`ContainerFilter` re-entrancy strip list is deferred.** The nested template render inside `ContainerFilter` passes the block's variables; without a designed reserved-key strip list, the inner render's model can carry `block_id`/`container` and re-trigger the filter (recursion for `container`, double debug markers). The minimal conversion keeps the inline literal `unset` from this migration; a follow-up should decide where the reserved-key list lives (private const in `ContainerFilter` vs. public const on `BlockPool`) and whether `tag`/`html_*` keys stay reserved.
- **Metadata variables leak into template scope.** Unlike v2 options, the metadata items are now regular variables and are visible as `$this->block_id`, `$this->order`, etc. in block templates. Templates and nested container-template renders see them unless stripped. Accepted as the cost of the simplification; document the reserved names.
- **Variable name collisions.** `block_id`, `parent`, `container`, `wrapper`, `tag` are now reserved on every block. Consumer code and templates must not use them for their own data.
- **`tag`/`html_*` options are inert.** After the `Container` block removal nothing renders them. Layout updates that relied on `class: container` must switch to `class: Laminas\View\Model\ViewModel` plus a container template via the `container` option, or to a custom block template.
- **Major BC breaks (already partly staged).** `BlockInterface`, `AbstractBlock`, `Container` are removed; `instanceof BlockInterface`/`AbstractBlock` and `setView`/`getView` calls in consumer code break; `BlockFactory` loses its third constructor parameter; `BlockManager` validates `ModelInterface` only. `blocks.container` service and `con-layout::container` template are gone.
