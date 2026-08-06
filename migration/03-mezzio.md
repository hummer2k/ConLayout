# Migration: laminas-mvc to laminas-mezzio

## Scope & baseline

- Package `hummer2k/conlayout` is a laminas-mvc module (ZF3 style). Target: usable as a Mezzio package, migrating in place with a major version bump to `^5.0`.
- The core (BlockPool, BlockFactory, Layout, LayoutUpdater, generators, collectors, filters, view helpers, proxies, Sorter, ModuleOptions, BlockManager) is built on `laminas-view`, `laminas-servicemanager`, `laminas-eventmanager`, `laminas-config`, `laminas-stdlib` and is already Mezzio-compatible. Only ~8 files import `Laminas\Mvc\*`.
- MVC-only pieces: `src/Module.php` (ModuleManager feature interfaces, `ServiceListener`, `onBootstrap`, `MvcEvent`), all 4 listeners in `src/Listener/`, `src/Controller/Plugin/LayoutManager.php` (extends `AbstractPlugin`), `src/BlockManagerFactory.php` (extends MVC `AbstractPluginManagerFactory`), `src/Ldt/Collector/LayoutCollector.php` (Laminas Developer Tools, MVC-only), `src/ModuleManager/Feature/BlockProviderInterface.php`, `ModuleOptionsFactory` (`view_manager` fallback), `TranslateFilterFactory` (`MvcTranslator`), `controller_plugins`/`view_manager`/`laminas-developer-tools` config keys, test bootstrap (`ServiceManagerConfig` + ModuleManager).

## Decisions (confirmed)

1. **Rendering**: keep the `ViewModel` tree rendering model (blocks are `ViewModel`s, `capture_to` regions, `before`/`after` ordering). A `LayoutMiddleware` renders the root view model directly with `PhpRenderer` (the same engine MVC used) and returns an `HtmlResponse`. Full feature parity, layout/block templates stay unchanged.
2. **Pipeline instead of listeners**: the 4 MVC listeners become 4 PSR-15 middleware classes with identical responsibilities.
3. **Handler contract**: any PSR-15 handler returning an `HtmlResponse` gets its body captured as the `content` region. For advanced use, ConLayout ships a `ViewModelResponse` (PSR-7 response wrapping a `ViewModel`).
4. **Per-request state**: services stay singletons (same as MVC, fine for PHP-FPM). A `LayoutContext` request attribute carries the facade so middleware stay stateless per request (RoadRunner/Swoole support later).
5. **LDT collector**: delete it; Mezzio has no laminas-developer-tools.

## Strategy

Replace the module entry point with a Mezzio `ConfigProvider`, convert the 4 `MvcEvent` listeners to PSR-15 middleware, drop all laminas-mvc/laminas-modulemanager/laminas-developer-tools dependencies, rewrite the test bootstrap to a plain ServiceManager + ConfigProvider setup.

Verification after each phase: `./vendor/bin/phpunit` and `./vendor/bin/phpcs --standard=PSR12 ./src/ ./test/`.

## Phase 1: Composer dependencies

- Remove: `laminas-mvc`, `laminas-modulemanager`, `laminas-router`, `laminas-http`, `laminas-developer-tools` (dev).
- Add: `mezzio/mezzio` (includes router, PSR-7), `mezzio/mezzio-laminasview` (template paths config, view helper manager, `PhpRenderer` service).
- Promote `laminas/laminas-filter` from suggest to require (filters are core); keep `laminas-i18n` as suggest (translate filter).
- Remove `extra.zf.component`; update keywords/description; bump version to `^5.0`; regenerate composer.lock.

## Phase 2: ConfigProvider + service wiring

- New `src/ConfigProvider.php`, delete `src/Module.php`. `__invoke()` returns:
  - `dependencies`: factories/aliases/invokables from `config/service.config.php`
  - `blocks`: BlockManager plugin config
  - `con-layout`: defaults (config template stays in `config/con-layout.global.php.dist`)
  - `templates.paths`: register `view/`
  - `view_helpers`: from `config/viewhelper.config.php`
  - `middleware`: pipeline entries
- `BlockManagerFactory`: rewrite as plain PSR-11 factory `new BlockManager($container, $config['blocks'] ?? [])` (the `AbstractPluginManager` constructor is PSR-11 compatible). Delete `Module::init()` / `ServiceListener` / `BlockProviderInterface`; consumers merge their `blocks` config in their own ConfigProvider.
- Filters: no MVC `FilterManager` in Mezzio; register filters as plain services/aliases in the main container and inject the container into `ViewHelperGenerator` (its `filterArgs()` only needs `has()`/`get()`).
- `ModuleOptionsFactory`: drop the `view_manager.controller_map` fallback, read only `con-layout.*`. `ModuleOptions`: remove dead imports (`LayoutTemplateListener`, `LayoutUpdateListener`) and the `listeners` key (replaced by middleware config).
- `TranslateFilterFactory`: pull `Laminas\I18n\Translator\TranslatorInterface` if registered, else fall back to a no-op.
- `BasePathFilterFactory`: adapt to Mezzio base path (config value `con-layout.base_path` or a Mezzio helper based lookup).

## Phase 3: Middleware pipeline

New `src/Middleware/` with factories, replacing the 4 listeners. Pipeline order:

```
LayoutMiddleware (outermost)        <- was LoadLayoutListener (EVENT_RENDER)
RouteMiddleware (app-provided)
HandleExtractionMiddleware          <- was ActionHandlesListener (EVENT_DISPATCH, prio 1000)
BodyClassMiddleware                 <- was BodyClassListener
PrepareActionResultMiddleware       <- was PrepareActionViewModelListener (EVENT_DISPATCH, prio -300)
DispatchMiddleware (app-provided)
```

- `HandleExtractionMiddleware`: reads `Mezzio\Router\RouteResult` request attribute; derives handles from route name plus `controller`/`action` params. Extract the `InjectTemplateListener` logic (`mapController()`/`inflectName()`) into a reusable `ConLayout\Inflector\TemplateInflector` (the base class must go). On `RouteResult` failure or a `Throwable` attribute (from the app's error handler) it injects the error handle (was `EVENT_DISPATCH_ERROR`).
- `BodyClassMiddleware`: body class from the matched route name (was `BodyClassListener`).
- `PrepareActionResultMiddleware`: wraps dispatch; after `$handler->handle()`: if the response is a `ViewModelResponse` it registers the view model as the `action.result` block (respecting the `terminate()` flag), otherwise it captures the `HtmlResponse` body as `content`.
- `LayoutMiddleware` (outermost): sets the root `ViewModel` (layout template from `con-layout.layout_template` or the Mezzio `templates.layout`), calls `$layout->load()` (generators -> blocks -> inject per `capture_to`), renders the tree via `PhpRenderer` (from the mezzio-laminasview service, Mezzio template resolver), returns `HtmlResponse`. Skips if layout is not configured/enabled or the response is not HTML.
- New `ConLayout\Http\ViewModelResponse` (PSR-7 response carrying a `ViewModel`).
- All middleware operate on a `LayoutContext` request attribute (`LayoutManager` facade + handles) so they stay stateless per request.

## Phase 4: LayoutManager replacement

- `LayoutManager` drops `AbstractPlugin` (becomes a plain facade class), moves from `src/Controller/Plugin/` to `src/LayoutManager.php`; `LayoutManagerFactory` already is PSR-11 and stays.
- Exposed to handlers via: (a) request attribute set by a small `LayoutContextMiddleware`, (b) new view helper `layout()` (`src/View/Helper/Layout.php`), (c) `$container->get(LayoutManager::class)`.
- Remove `controller_plugins` config; update `docs/controller-plugin.md` accordingly.

## Phase 5: LDT removal

- Delete `src/Ldt/**`, `view/laminas-developer-tools/**`, the `laminas-developer-tools` config block, `LayoutCollector` + `LayoutCollectorFactory`, and `test/Ldt/Collector/LayoutCollectorTest.php`.

## Phase 6: Tests

- Rewrite `test/Bootstrap.php`: plain `ServiceManager` + `ConfigProvider` config (no ModuleManager/`ServiceManagerConfig`); `test/TestConfig.php.dist` becomes a Mezzio-style config fixture.
- `ModuleTest` -> `ConfigProviderTest` (assert config arrays and factory wiring).
- Listener tests -> middleware tests using PSR-7 `ServerRequest` with `RouteResult` attributes (`ActionHandlesListenerTest`, `BodyClassListenerTest`, `LoadLayoutListenerTest`, `PrepareActionViewModelListenerTest`).
- `LayoutManagerTest`: plain service test instead of the MVC `PluginManager`. `test/Controller/TestAsset/SampleController.php` -> PSR-15 `SampleHandler`.
- `LayoutCollectorTest` deleted. All core tests (Block, Layout, Updater, generators, filters, view helpers) keep their assertions, only the bootstrap changes.

## Phase 7: Docs, CI, housekeeping

- Rewrite `docs/installation.md`, `docs/integration.md`, `docs/controller-plugin.md` (middleware/view-helper usage), update code samples in `docs/areas.md`, `docs/acl.md`, `docs/caching.md`.
- Update `Readme.md` (version `^5.0`, Mezzio installation via `config/autoload/`), `AGENTS.md`, `CHANGELOG.md`.
- CI keeps `phpunit` + `phpcs` commands; composer.lock regenerated.
- Fix stale `.gitignore` `tests/` entry (real directory is `test/`).

## BC impact & release

This is a major BC break for the published composer package: the module entry point (`Module` class, ModuleManager integration), the 4 listeners, the controller plugin and the LDT collector disappear; consumers must wire the middleware pipeline and use the `LayoutManager` service/attribute/view helper instead.

- Version bump to `v5.0.0` with a migration note in `docs/integration.md`.
- Package name stays `hummer2k/conlayout`; migration happens in place.

## Risks

- Rendering parity: the `ViewModel` tree rendering via `PhpRenderer` must reproduce MVC behavior (children rendered per `capture_to`, `append` flag, `ContainerFilter`/`DebugFilter` filter chain). The filter chain attachment moves from `Module::onBootstrap()` to a `PhpRenderer` delegator factory.
- Per-request state: singletons reuse `BlockPool`/`Layout`/`LayoutUpdater` across requests in long-running workers; mitigated by the `LayoutContext` attribute and documented as a future per-request context object.
- Handler contract: capturing plain `HtmlResponse` bodies as `content` changes semantics for consumers not using ConLayout; gated by `con-layout.enabled`/layout template config.
- Mezzio service names: mezzio-laminasview provides `PhpRenderer`/helper manager services; verify the exact service names on the target Mezzio version during Phase 2.
- Error detection: depends on the `Throwable` request attribute convention of the app's error handler middleware (configurable attribute key).
