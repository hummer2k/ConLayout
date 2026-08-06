# Migration: PHP language modernizations (8.0-8.2)

## Scope & baseline

- `06-viewmodel-vars.md` is applied. Constructor injection now uses promoted `private readonly` properties package-wide (commit `17cab3b`), matching `src/Layout/Layout.php`. `src/Block/BlockRegistry.php` sorts blocks within parent scope (commit `0a9b336`).
- This migration removes the remaining pre-8.0 idioms and marks immutable classes as `readonly class`. PHP floor stays at `^8.3` (composer.json), so everything in scope is long since supported.
- The package already ships a curated Rector config (`rector.php`, PHP 8.3, type-declaration rules only). Four additional rules are registered so future runs keep the new patterns.
- Out of scope: PHP 8.4 features (property hooks, asymmetric visibility), named arguments, enums. `stripos`/`substr` in `ToolbarMiddleware` (needs byte positions) and `substr(md5_file(...), 0, 8)` in `CacheBusterFilter` stay as-is.

## Decisions

1. **`str_contains`/`str_starts_with` replace `strpos` guards.** Three exact matches in `src/`. Position-based `stripos`/`substr` usages keep the old functions.
2. **First-class callable syntax replaces `call_user_func`/`[$this, 'method']` arrays.** Applies to the invokable `BasePathHelper`, the `Sorter` callback array and the test helpers.
3. **Arrow functions replace single-expression closures.** The two event-manager callbacks return one boolean expression.
4. **Spaceship operator plus null coalescing replace hand-rolled `uasort` comparators.** `($a['order'] ?? 1) <=> ($b['order'] ?? 1)`. Nuance accepted: the old code compared with loose `==`/`<`, the spaceship is strict; both only differ for non-numeric `order` values, and layout-XML orders are numeric. The sorter tests pin the behavior.
5. **`$instance::class` replaces `get_class($instance)`** in `NamedParametersTrait` (PHP 8.0 object `::class`).
6. **Classes whose properties are all readonly become `readonly class`** (PHP 8.2). The promoted properties drop the redundant `readonly` keyword. **BC risk accepted:** a readonly class can only be extended by another readonly class, so consumer subclasses of public classes (`LayoutManager`, the middleware/filter classes) break at compile time. This is a major-version break, consistent with the rest of the v3 migration.
7. **`ViewModelResponse` is excluded from the readonly conversion.** PHP rejects `readonly class` extending a non-readonly parent, and `laminas-diactoros Response` is not readonly. Classes with any mutable state (`LayoutContext`, `FilesystemCollector`, `CacheBusterFilter`, `BlockFactory`, `ViewHelperGenerator`, `Wrapper`, `BodyClass`, `BlockRegistry`, `Layout`, `ModuleOptions`, `LayoutUpdater`, `UpdateEvent`) stay plain classes.
8. **Tests are modernized too.** The `call_user_func`/`call_user_func_array` usages in the view-helper tests follow the same pattern as `src/`; CI runs the test suite.
9. **Rector rules are registered but the diff stays manual.** `StrContainsRector`, `ClassOnObjectRector`, `ClosureToArrowFunctionRector`, `ArrayToFirstClassCallableRector` are added to `rector.php`. A dry run after the manual pass must report no changes.

## Implementation

### `src/Block/BlockRegistry.php`

- `sort()`: replace the `uasort($group, function ($a, $b) { ... })` block with
  ```php
  uasort($group, fn ($a, $b) => $a->getVariable('order', 0) <=> $b->getVariable('order', 0));
  ```
  and drop the now-redundant `/* @var $a ModelInterface */` / `/* @var $b ModelInterface */` comments.
- `getParentId()`: `if (false !== strpos($block->captureTo(), LayoutInterface::CAPTURE_TO_DELIMITER))` becomes `if (str_contains($block->captureTo(), LayoutInterface::CAPTURE_TO_DELIMITER))`.

### `src/Filter/ContainerFilter.php`

- `filter()`: `if (false !== strpos($container, '<'))` becomes `if (str_contains($container, '<'))`.
- Class becomes `readonly class ContainerFilter implements FilterInterface`; constructor becomes `public function __construct(private PhpRenderer $phpRenderer, private ViewModelHelper $viewModelHelper) {}`.

### `src/Filter/DebugFilter.php`

- `filter()`: `if (0 !== strpos($value, $opening))` becomes `if (!str_starts_with($value, $opening))`.
- Class becomes `readonly class DebugFilter implements FilterInterface`; constructor as in `ContainerFilter`.

### `src/Filter/BasePathFilter.php`

- `filter()`: `return call_user_func($this->basePathHelper, $value);` becomes `return ($this->basePathHelper)($value);`.
- Class becomes `readonly class BasePathFilter implements FilterInterface` with promoted `private BasePathHelper $basePathHelper`.

### `src/Filter/TranslateFilter.php`

- Class becomes `readonly class TranslateFilter implements FilterInterface` with promoted `private TranslatorInterface $translator`.

### `src/Filter/CacheBusterFilter.php`

- No change (`$rawValue` stays mutable through `setRawValue()`).

### `src/Updater/LayoutUpdater.php`

- `getLayoutStructure()`: the trigger callback
  ```php
  function ($result) {
      return ($result instanceof Config);
  }
  ```
  becomes `fn ($result) => $result instanceof Config`.

### `src/Updater/Collector/ConfigCollector.php`

- Class becomes `readonly class ConfigCollector implements CollectorInterface` with promoted `private array $config`.

### `src/Updater/Collector/FilesystemCollector.php`

- No change (`$collectedFiles` stays mutable through `collect()`).

### `src/Generator/BlocksGenerator.php`

- Class becomes `readonly class BlocksGenerator implements GeneratorInterface` with promoted `private BlockFactoryInterface $blockFactory` / `private BlockRegistryInterface $blockRegistry`.

### `src/Generator/ViewHelperGenerator.php`

- No change (`$filterManager`, `$sorter`, `$debug` stay mutable through setters).

### `src/Block/Factory/BlockFactory.php`

- `configure()`: the trigger callback
  ```php
  function ($result) {
      return $result instanceof ModelInterface;
  }
  ```
  becomes `fn ($result) => $result instanceof ModelInterface`.
- No readonly class (`$blockDefaults` is merged in the constructor).

### `src/Handle/Handle.php`

- Class becomes `final readonly class Handle implements HandleInterface`; constructor becomes `public function __construct(private string $name, private int $priority) {}`.

### `src/LayoutManager.php`

- Class becomes `readonly class LayoutManager implements LayoutInterface, LayoutUpdaterInterface`; constructor drops the `readonly` keywords.

### `src/Layout/Layout.php`

- `getBlocks()`: malformed docblock `@return <ModelInterface>` becomes `@return ModelInterface[]`.

### `src/Middleware/LayoutContextMiddleware.php`

- Class becomes `readonly class LayoutContextMiddleware implements MiddlewareInterface`; constructor becomes `public function __construct(private LayoutManager $layoutManager) {}`.

### `src/Middleware/HandleExtractionMiddleware.php`

- Class becomes `readonly class HandleExtractionMiddleware implements MiddlewareInterface`; constructor drops the `readonly` keywords.

### `src/Middleware/LayoutMiddleware.php`

- Class becomes `readonly class LayoutMiddleware implements MiddlewareInterface`; constructor drops the `readonly` keywords.

### `src/Middleware/PrepareMainContentMiddleware.php`

- Class becomes `readonly class PrepareMainContentMiddleware implements MiddlewareInterface` with promoted `private BlockRegistryInterface $blockRegistry`.

### `src/Middleware/BodyClassMiddleware.php`

- Class becomes `readonly class BodyClassMiddleware implements MiddlewareInterface`; constructor drops the `readonly` keywords.

### `src/Sorter/OrderComparison.php`

- `sort()`: the closure becomes
  ```php
  uasort($data, fn ($a, $b) => ($a['order'] ?? 1) <=> ($b['order'] ?? 1));
  ```

### `src/Sorter/BeforeAfterComparison.php`

- `sort()`: `uasort($configArray, [$this, 'compare'])` becomes `uasort($configArray, $this->compare(...))`.

### `src/NamedParametersTrait.php`

- `invokeArgs()`: `$class = get_class($instance);` becomes `$class = $instance::class;`.

### `src/View/Helper/Block.php`

- Class becomes `readonly class Block` with promoted `private BlockRegistryInterface $blockRegistry`.

### `src/View/Helper/Layout.php`

- Class becomes `readonly class Layout` with promoted `private LayoutManager $layoutManager`.

### `src/View/Helper/Wrapper.php`

- No change (`$tag` stays mutable through `__invoke()`).

### `src/Toolbar/ToolbarData.php`

- Class becomes `readonly class ToolbarData`; constructor drops the `readonly` keywords.

### `src/Toolbar/ToolbarMiddleware.php`

- Class becomes `readonly class ToolbarMiddleware implements MiddlewareInterface`; constructor drops the `readonly` keywords. `stripos`/`substr` insertion logic unchanged.

### `src/Http/LayoutContext.php`

- No change (`$handles` stays mutable through `setHandles()`/`addHandle()`).

### `src/Http/ViewModelResponse.php`

- No change (readonly class cannot extend non-readonly `Response`).

### Tests

- `test/View/Helper/WrapperTest.php`: `call_user_func($this->wrapperHelper)` / `call_user_func($this->wrapperHelper, $tag)` become `($this->wrapperHelper)()` / `($this->wrapperHelper)($tag)` (3 sites).
- `test/View/Helper/BlockTest.php`: `call_user_func_array($this->blockHelper, ['test.block'])` becomes `($this->blockHelper)('test.block')`.
- No other test changes.

### `rector.php`

- Add to `withRules()`:
  - `Rector\Php80\Rector\NotIdentical\StrContainsRector`
  - `Rector\Php80\Rector\FuncCall\ClassOnObjectRector`
  - `Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector`
  - `Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector`
- Rule names verified against the installed Rector 2.6.1 (`rules/Php80/...`, `rules/Php74/...`, `rules/Php81/...`).
- Deliberately not registered: `ReadOnlyClassRector` (would auto-convert future all-readonly classes and re-introduce the BC break without review), `CallUserFuncToMethodCallRector`/`CallUserFuncArrayToVariadicRector` (target `[$obj, 'method']` arrays, not invokable objects).

## Verification

- From `vendor/hummer2k/conlayout/`: `./vendor/bin/phpunit` (whole suite green).
- `composer cs-check` / `composer cs-fix` (php-cs-fixer, PER Coding Style).
- `./vendor/bin/rector process --dry-run --no-progress-bar` reports `[OK] Rector is done!` with no changed files.
- `php -l` on every touched file via the phpunit run (already covered).

## Commit plan

1. `refactor: mark immutable classes as readonly` (the 17 readonly-class conversions, step 6).
2. `refactor: modernize to PHP 8.x language features` (string functions, first-class callables, arrow functions, spaceship comparators, `$instance::class`, docblock fix, test helpers, Rector rules).

## Risks & open points

- **BC break from `readonly class`.** Readonly classes can only be extended by readonly classes. Consumer code subclassing `LayoutManager`, `ToolbarMiddleware`, the filters or the middleware breaks with a fatal error at compile time. Accepted by decision, aligned with the major-version nature of the v2 to v3 migration.
- **Spaceship is strict, old comparison was loose.** For non-numeric `order` values the sort result can differ (`'1' <=> '2'` vs `'1' == '2'`). Layout-XML orders are numeric; the sorter tests stay green.
- **`str_starts_with`/`str_contains` are byte-oriented, like `strpos`.** No behavior change for the ASCII markers used (`</body>` was already position-based and untouched).
- **Rector config grows opinionated.** The four rules encode the new idioms; future runs of `rector process` (no CI step exists) keep them. Running `rector process` after this migration is expected to be a no-op.
