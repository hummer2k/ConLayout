# AGENTS.md

Mezzio package implementing Magento-style layout blocks/handles. Distributed as the Composer package `hummer2k/conlayout`; `ConLayout\` maps to `src/`, `ConLayoutTest\` to `test/`.

## Commands

- `composer install` (vendor is not committed; no `composer.lock`-driven scripts)
- `./vendor/bin/phpunit` runs the whole suite. Bootstrap (`test/Bootstrap.php`) boots a plain ServiceManager with the merged `ConLayout\ConfigProvider` + `Mezzio\LaminasView\ConfigProvider` config; `test/TestConfig.php.dist` is a Mezzio-style config fixture. `src/Sorter/` is excluded from the coverage whitelist.
- `composer cs-check` (php-cs-fixer, dry-run) is the only lint gate; `composer cs-fix` applies fixes. Ruleset is `@PER-CS` (PER Coding Style, `https://www.php-fig.org/per/coding-style/`) configured in `.php-cs-fixer.dist.php`, scoped to `src/` and `test/`. CI also runs `phpunit --coverage-clover ./build/logs/clover.xml`.
- Run order in CI: phpunit, then php-cs-fixer.

## Environment

- Project targets PHP `^8.2` (checked on 8.2, 8.3 and 8.4 in CI). `composer install` and `./vendor/bin/phpunit` (PHPUnit 11) run directly on the local PHP 8.4.
- CI runs on GitHub Actions (`.github/workflows/ci.yml`).
- Mezzio 3 + `mezzio-laminasviewrenderer` 2 (keeps laminas-view 2.x and laminas-servicemanager 3.x).

## Architecture

- `src/ConfigProvider.php` is the wiring hub: `__invoke()` returns `dependencies` (from `config/service.config.php` + `config/filter.config.php`), `blocks` (BlockManager config), `con-layout` defaults (template stays in `config/con-layout.global.php.dist`), `templates` (view path + template map), `view_helpers` (from `config/viewhelper.config.php`) and `middleware` (pipeline entries in order).
- Request flow: the middleware pipeline is `LayoutContextMiddleware` (outermost, sets the `LayoutContext` request attribute) → `LayoutMiddleware` (renders the root view model tree via the `PhpRenderer`) → `HandleExtractionMiddleware` (handles from the `RouteResult` route name or `controller`/`action` params, error handles from the configurable error attribute) → `BodyClassMiddleware` → `PrepareMainContentMiddleware` (registers `ViewModelResponse` view models or captured `HtmlResponse` bodies as the `main.content` block). Consumers pipe the Mezzio `RouteMiddleware`/`DispatchMiddleware` in between.
- Core (BlockRegistry, BlockFactory, Layout, LayoutUpdater, generators, collectors, filters, view helpers, proxies, Sorter, ModuleOptions, BlockManager) is Mezzio-compatible; no laminas-mvc imports remain.
- The `PhpRenderer` service is created via a delegator (`src/View/PhpRendererDelegatorFactory.php`) that attaches `ContainerFilter` (and `DebugFilter` when `con-layout.debug` is enabled) to the renderer filter chain.
- `LayoutManager` is a plain facade class (`src/LayoutManager.php`) exposed via the `LayoutContext` request attribute, the `layout()` view helper and the container.
- Key identifiers: root view model block id is `root`, controller result is `main.content`, `capture_to` syntax is `blockId::captureTo` (`LayoutInterface` constants). Layout updates are XML by default; only `xml` is enabled in `layout_update_extensions` (PHP/YAML can be added).

## Conventions

- Factories are one per class (`FooFactory` next to `Foo`), registered in `config/service.config.php`.
- The four layout instructions (`blocks`, `helpers`, `include`, `reference`) are documented in `docs/layout-instructions.md`; also `docs/areas.md`, `docs/integration.md`, `docs/controller-plugin.md`, `docs/caching.md`, `docs/acl.md`, `docs/installation.md`, `docs/toolbar.md`.
- Custom block templates go into `view/blocks/` (registered template path stack).
