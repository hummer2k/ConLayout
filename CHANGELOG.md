# Changelog

All notable changes to this project will be documented in this file.

## [v5.0.0] - Unreleased

This is a major BC break. All sources and tests now use `declare(strict_types=1)`, native parameter and return type declarations, and typed properties. Please check the following before upgrading:

- Method parameters are now strictly typed (e.g. `Layout::addBlock(string $blockId, ModelInterface $block)`). Loosely typed call sites (e.g. string priorities passed to `attachGenerator`) now throw a `TypeError`.
- Fluent methods (setters, `addBlock`, `load`, ...) now return `static` instead of the concrete class.
- Consumers implementing the project interfaces (`LayoutInterface`, `LayoutUpdaterInterface`, `BlockPoolInterface`, `GeneratorInterface`, `SorterInterface`, `CollectorInterface`, `HandleInterface`, `BlockFactoryInterface`, `BlockProviderInterface`, `RawValueAwareInterface`) must match the new typed signatures.
- Subclasses of `AbstractBlock`, `Layout`, `ModuleOptions` or the listeners must match the new typed properties and method signatures (e.g. `AbstractBlock::$view` is now `?RendererInterface`, `LayoutUpdater::$layoutStructure` is now `?Config`).
- `ModuleOptions::getRemoteCall()` is now `bool|string`, `BlockPool::get()` is now `array|ModelInterface|false`.
- Factory `__invoke` signatures now use `Psr\Container\ContainerInterface` instead of the removed `Interop\Container\ContainerInterface`.
- `Layout::generate()` now returns `static` (previously declared no return type).
