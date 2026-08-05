# Changelog

All notable changes to this project will be documented in this file.

## [v5.0.0] - Unreleased

Migration from laminas-mvc to Mezzio. This is a major BC break.

### Added

- `ConLayout\ConfigProvider` replaces the `ConLayout\Module` class; register it
  in your Mezzio config aggregator.
- PSR-15 middleware pipeline: `LayoutContextMiddleware`, `LayoutMiddleware`,
  `HandleExtractionMiddleware`, `BodyClassMiddleware` and
  `PrepareActionResultMiddleware` (replacing the MVC event listeners).
- `ConLayout\Http\ViewModelResponse`, a PSR-7 response wrapping a
  `Laminas\View\Model\ViewModel`; handlers may return it instead of an
  `HtmlResponse`.
- `ConLayout\Http\LayoutContext`, a per-request facade carried as request
  attribute.
- `layout()` view helper and `ConLayout\LayoutManager` service/facade
  (replacing the `layoutManager()` controller plugin).
- `PhpRenderer` delegator factory attaching the `ContainerFilter`/`DebugFilter`
  filter chain to the renderer.

### Changed

- Dependencies: removed `laminas-mvc`, `laminas-modulemanager`, `laminas-router`,
  `laminas-http` and `laminas-developer-tools`; added `mezzio/mezzio`,
  `mezzio/mezzio-laminasviewrenderer` and `laminas/laminas-diactoros`;
  `laminas/laminas-filter` is now a required dependency.
- Layout and block templates stay unchanged; the layout update XML format and
  the `blocks`, `helpers`, `include` and `reference` instructions are
  unchanged.
- `ModuleOptions`: the `listeners` option was removed (replaced by the
  middleware config); added `enabled`, `layout_template`, `base_path` and
  `error_handle_attribute`. The dead `controller_map` and
  `prefer_route_match_controller` options were removed.
- Action handles derive from the dot-separated route name; handle name
  inflection uses `Laminas\Filter\Word\CamelCaseToDash` instead of the copied
  `ConLayout\Inflector\TemplateInflector`.
- `BasePathFilter` reads the base path from `con-layout.base_path`.
- `TranslateFilterFactory` falls back to a no-op translator when no
  `Laminas\I18n\Translator\TranslatorInterface` is registered.
- Version bumped to `5.0.0`.

### Removed

- The `options` key in block definitions and `block_defaults`. laminas-view 3
  dropped `ViewModel` options; set block variables via `variables` instead.
- `ConLayout\Module` and the ModuleManager integration.
- `ConLayout\Inflector\TemplateInflector`.
- The four MVC listeners (`ActionHandlesListener`, `BodyClassListener`,
  `LoadLayoutListener`, `PrepareActionViewModelListener`).
- The `layoutManager()` controller plugin.
- The laminas-developer-tools collector (`src/Ldt`).
- The `controller_plugins`, `view_manager` and `laminas-developer-tools`
  configuration keys.

## [v4.0.0] - Unreleased

This is a major BC break. All sources and tests now use `declare(strict_types=1)`, native parameter and return type declarations, and typed properties. Please check the following before upgrading:

- Method parameters are now strictly typed (e.g. `Layout::addBlock(string $blockId, ModelInterface $block)`). Loosely typed call sites (e.g. string priorities passed to `attachGenerator`) now throw a `TypeError`.
- Fluent methods (setters, `addBlock`, `load`, ...) now return `static` instead of the concrete class.
- Consumers implementing the project interfaces (`LayoutInterface`, `LayoutUpdaterInterface`, `BlockPoolInterface`, `GeneratorInterface`, `SorterInterface`, `CollectorInterface`, `HandleInterface`, `BlockFactoryInterface`, `BlockProviderInterface`, `RawValueAwareInterface`) must match the new typed signatures.
- Subclasses of `AbstractBlock`, `Layout`, `ModuleOptions` or the listeners must match the new typed properties and method signatures (e.g. `AbstractBlock::$view` is now `?RendererInterface`, `LayoutUpdater::$layoutStructure` is now `?Config`).
- `ModuleOptions::getRemoteCall()` is now `bool|string`, `BlockPool::get()` is now `array|ModelInterface|false`.
- Factory `__invoke` signatures now use `Psr\Container\ContainerInterface` instead of the removed `Interop\Container\ContainerInterface`.
- `Layout::generate()` now returns `static` (previously declared no return type).
