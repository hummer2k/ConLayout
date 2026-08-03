# AGENTS.md

Laminas MVC (ZF3) module implementing Magento-style layout blocks/handles. Distributed as the Composer package `hummer2k/conlayout`; `ConLayout\` maps to `src/`, `ConLayoutTest\` to `test/`.

## Commands

- `composer install` (vendor is not committed; no `composer.lock`-driven scripts)
- `./vendor/bin/phpunit` runs the whole suite. Bootstrap (`test/Bootstrap.php`) boots a real ServiceManager + ModuleManager and loads `test/TestConfig.php.dist` directly, so no config copy is needed. `src/Sorter/` is excluded from the coverage whitelist.
- `./vendor/bin/phpcs --standard=PSR12 ./src/ ./test/` is the only lint gate (CI also runs `phpunit --coverage-clover ./build/logs/clover.xml`).
- Run order in CI: phpunit, then phpcs.

## Environment

- Project targets PHP `^8.2` (checked on 8.2, 8.3 and 8.4 in CI). `composer install` and `./vendor/bin/phpunit` (PHPUnit 11) run directly on the local PHP 8.4.
- CI runs on GitHub Actions (`.github/workflows/ci.yml`).
- `.gitignore` references `/tests/TestConfig.php`, but the real directory is `test/` (singular); the ignore is stale.

## Architecture

- `src/Module.php` is the wiring hub: `getConfig()` merges `config/module.config.php` with `config/con-layout.global.php.dist` (the dist file doubles as default config and user template; consumers override it with their own `con-layout.global.php`).
- `Module::init()` registers a dedicated `BlockManager` service manager fed by the `blocks` config key via `ModuleManager\Feature\BlockProviderInterface`.
- Bootstrap attaches only the listeners enabled in `con-layout.listeners` (default: all three: `ActionHandlesListener`, `LoadLayoutListener`, `PrepareActionViewModelListener`) and wires the `ContainerFilter` into the PhpRenderer filter chain.
- Request flow: `LayoutUpdater` collects layout update files from area paths (`layout_update_paths`, per area: `global`/`frontend`/`backend`/`default`) → generators (`BlocksGenerator`, `ViewHelperGenerator`) build the view model tree → blocks are injected. Areas are set at runtime via `$layoutUpdater->setArea()`.
- Layout updates are XML by default; only `xml` is enabled in `layout_update_extensions` (PHP/YAML can be added).
- Key identifiers: root view model block id is `root`, controller result is `action.result`, `capture_to` syntax is `blockId::captureTo` (`LayoutInterface` constants).
- Controller plugin `layoutManager` and view-helper proxies (`HeadLinkProxy`, etc.) are configured in `con-layout.view_helpers`.

## Conventions

- Factories are one per class (`FooFactory` next to `Foo`), registered in `config/service.config.php`.
- The four layout instructions (`blocks`, `helpers`, `include`, `reference`) are documented in `docs/layout-instructions.md`; also `docs/areas.md`, `docs/integration.md`, `docs/controller-plugin.md`, `docs/caching.md`, `docs/acl.md`.
- Custom block templates go into `view/blocks/` (registered template path stack).
