# AGENTS.md

Mezzio package implementing Magento-style layout blocks/handles. Distributed as the Composer package `hummer2k/conlayout`; `ConLayout\` maps to `src/`, `ConLayoutTest\` to `test/`.

## Commands

- `composer install` (vendor is not committed; the committed `composer.lock` is pinned to platform PHP 8.3 and is used by CI)
- `./vendor/bin/phpunit` runs the whole suite. Bootstrap (`test/Bootstrap.php`) boots a plain ServiceManager with the merged `ConLayout\ConfigProvider` + `Mezzio\LaminasView\ConfigProvider` config; `test/TestConfig.php.dist` is a Mezzio-style config fixture. `src/Sorter/` is excluded from the coverage whitelist.
- `composer cs-check` (php-cs-fixer, dry-run) is the only lint gate; `composer cs-fix` applies fixes. Ruleset is `@PER-CS` (PER Coding Style, `https://www.php-fig.org/per/coding-style/`) configured in `.php-cs-fixer.dist.php`, scoped to `src/` and `test/`. CI also runs `phpunit --coverage-clover ./build/logs/clover.xml`.
- `./vendor/bin/psalm` (vimeo/psalm 6.x, error level 1) runs static analysis; config is `psalm.xml`, scoped to `src/`.
- Run order in CI: phpunit, then php-cs-fixer.

## Environment

- Project targets PHP `^8.3` (checked on 8.3, 8.4 and 8.5 in CI). `composer install` and `./vendor/bin/phpunit` (PHPUnit 11) run directly on the local PHP 8.4.
- CI runs on GitHub Actions (`.github/workflows/ci.yml`).
- Mezzio 3 + `mezzio-laminasviewrenderer` 3 (laminas-view 3.x and laminas-servicemanager 4.x).

## Architecture

- `src/ConfigProvider.php` is the wiring hub: `__invoke()` returns `dependencies` (from `config/service.config.php` + `config/filter.config.php`), `blocks` (BlockManager config), `con-layout` defaults (template stays in `config/con-layout.global.php.dist`), `templates` (view path + template map), `view_helpers` (from `config/viewhelper.config.php`) and `middleware` (pipeline entries in order).
- `XmlPreprocessor` (`src/Updater/Collector/`) normalizes the typed XML dialect. Each typed element (`block`, `css`, `js`, `title`, `reference`) is a class in `src/Updater/Collector/Element/` implementing `ElementInterface` (extend `AbstractElement` for the shared helpers); the list is pluggable via `con-layout.xml_preprocessor.elements`, resolved by `XmlPreprocessorFactory`.
- Request flow: the middleware pipeline is `ToolbarMiddleware` (outermost, injects the debug toolbar into HTML responses when enabled) → `LayoutContextMiddleware` (sets the `LayoutContext` request attribute) → `LayoutMiddleware` (renders the root view model tree via the `PhpRenderer`) → `HandleExtractionMiddleware` (handles from the `RouteResult` route name or `controller`/`action` params, error handles from the configurable error attribute) → `BodyClassMiddleware` → `PrepareMainContentMiddleware` (registers `ViewModelResponse` view models or captured `HtmlResponse` bodies as the `main.content` block). Consumers pipe the Mezzio `RouteMiddleware`/`DispatchMiddleware` in between.
- Core (BlockRegistry, BlockFactory, Layout, LayoutUpdater, generators, collectors, filters, view helpers, proxies, Sorter, ModuleOptions, BlockManager) is Mezzio-compatible; no laminas-mvc imports remain.
- The `PhpRenderer` service is created via a delegator (`src/View/PhpRendererDelegatorFactory.php`) that attaches `ContainerFilter` (and `DebugFilter` when `con-layout.debug` is enabled) to the renderer filter chain.
- `LayoutManager` is a plain facade class (`src/LayoutManager.php`) exposed via the `LayoutContext` request attribute, the `layout()` view helper and the container.
- Key identifiers: root view model block id is `root`, controller result is `main.content`, `captureTo` syntax is `blockId::captureTo` (`LayoutInterface` constants). Layout updates are XML by default; only `xml` is enabled in `layout_update_extensions` (PHP/YAML can be added).

## Conventions

- Factories are one per class (`FooFactory` next to `Foo`), registered in `config/service.config.php`.
- The typed XML elements (`block`, `css`, `js`, `title`, `reference`, `include`) are documented in `docs/layout-instructions.md`; also `docs/areas.md`, `docs/integration.md`, `docs/controller-plugin.md`, `docs/caching.md`, `docs/acl.md`, `docs/installation.md`, `docs/toolbar.md`.
- Custom block templates go into `view/blocks/` (registered template path stack).
