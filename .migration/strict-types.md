# Migration: Modern PHP 8.2+ code (strict types, native type hints, native return types)

## Scope & baseline

- 118 classes/interfaces/traits in `src/` (74 files), 44 test files.
- 0 src files have `declare(strict_types=1)` (only `test/Filter/ContainerFilterTest.php`), 332 methods in `src/` lack return types, most params untyped (docblock annotations only).
- Target PHP is already `^8.2`; CI runs PHPUnit 11 + phpcs PSR12 on 8.2/8.3/8.4 (`.github/workflows/ci.yml`).
- Some files are partially typed already (`Module::attachFilter(int $priority): void`, `ModuleOptions::getBodyClassPrefix(): string`, `LayoutCollector` private helpers). This sets the style to follow.

## Strategy

Hybrid approach: Rector automates the mechanical conversion (estimated 80%), a manual review pass handles the semantic 20% (nullability, unions, interface contracts, Laminas variance). Contract-first: type the project interfaces after their implementations, then verify.

Verification after each phase: `./vendor/bin/phpunit` and `./vendor/bin/phpcs --standard=PSR12 ./src/ ./test/`.

## What Rector automates

- `declare(strict_types=1)` (`StrictTypesRector`)
- Typed properties from `@var` docblocks and constructor/assigns
- Return types provable from code: strict returns, `return $this` (fluent to `static`), `return new X`, native calls, `void` where no return statement exists
- Docblock/syntax cleanup (`RemoveUselessParamDocRector`, `ListToArrayDestructRector`, `ArrayShortSyntaxRector`)
- Safe param typing from strict native call sites

## What Rector does NOT do (manual pass)

- Nullability nuances:
  - `AbstractBlock::$view` must become `?RendererInterface` (null until `setView()`), Rector would produce a non-nullable typed property from the docblock, causing "must not be accessed before initialization"
  - `LayoutUpdater::$layoutStructure` -> `?Config`
- Union/mixed returns Rector cannot prove:
  - `BlockPool::get()` -> `ModelInterface[]|ModelInterface|false`
  - `ModuleOptions::getRemoteCall()` -> `bool|string`
  - `ContainerFilter`/`DebugFilter::filter()` pass `$value` through unchanged -> `: mixed`
  - `Layout::isAllowed()` returns `$result->last()` (mixed) -> cast `(bool)`
- Interface contracts: Rector types implementations individually; the 8 project interfaces need hand-typed signatures matching the implementations, including fluent `static` unification (`Layout::generate()` must return `$this` so `LayoutInterface::generate(): static` is consistent with `LayoutManager::generate()`).
- Docblock-only params (Rector 2.x removed unsafe docblock param typing): `ModuleOptions` setters (`setDebug($debug)` etc.), `Handle::__construct($name, $priority)`, `Layout` methods, ...
- Magic methods: `__call(string $name, array $arguments): mixed` (`AbstractViewHelperProxy`), `__invoke` types (`Block::__invoke(?string): ModelInterface|false`, `Wrapper::__invoke(?string $tag = null): static`, `LayoutManager::__invoke(): static`)
- Laminas variance constraints: Rector respects parent signatures, but verify in the dry-run diff that these params stayed untyped:
  - `ListenerAggregateInterface::attach(EventManagerInterface $events, $priority = 1)` (all 4 listeners)
  - `FilterInterface::filter($value)` (5 filters)
  - `FactoryInterface::__invoke(ContainerInterface $container, $requestedName, ?array $options = null)` (all 20 factories)
  - Return types can be added everywhere (covariance with implicit `mixed`), e.g. `__invoke(...): LayoutCollector`, `onBootstrap(EventInterface $e): void`, `getConfig(): array`, `init(ModuleManagerInterface $manager): void`
- Pre-existing bugs the typing surfaces:
  - `BlocksGenerator::isRemoved()` returns `null` from `filter_var($remove, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)` -> `(bool) filter_var(...) ?? false` for `: bool`
  - Test stubs/anonymous classes implementing project interfaces must match the new interface signatures

## Critical caveat

Do NOT use full version sets (`SetList::PHP_82` etc.). They contain behavior-changing rules (`RandomFunctionRector`, readonly classes, enums). The `rector.php` config must use targeted individual type-declaration rules only.

## Project interface target signatures (hand pass)

| Interface | Target signatures |
|---|---|
| `LayoutInterface` | `getBlock(string): ModelInterface\|false`, `getBlocks(): array`, `addBlock(string, ModelInterface): static`, `removeBlock(string): static`, `generate(array $generators = []): static`, `injectBlocks(): void`, `load(): static`, `setRoot(ModelInterface): static`, `attachGenerator(string, GeneratorInterface, int $priority = 1): static`, `detachGenerator(string): static` |
| `LayoutUpdaterInterface` | `getLayoutStructure(): Config`, `addHandle(HandleInterface): static`, `setHandles(array): static`, `removeHandle(string): static`, `getHandles(bool $asObject = false): array`, `setArea(string): static`, `getArea(): string` |
| `BlockPoolInterface` | `get(?string $blockId = null): ModelInterface[]\|ModelInterface\|false`, `add(string, ModelInterface): static`, `remove(string): void`, `sort(): static` |
| `GeneratorInterface` | `generate(Config): void` |
| `SorterInterface` | `sort(array): array` |
| `CollectorInterface` | `collect(string $handle, ?string $area = null): Config` |
| `HandleInterface` | `getName(): string`, `getPriority(): int`, `__toString(): string` |
| `BlockFactoryInterface` | `createBlock(string, array): ModelInterface`, `configure(ModelInterface, array): ModelInterface` |
| `BlockProviderInterface` | `getBlockConfig(): array` |

`LayoutManager` implements both `LayoutInterface` and `LayoutUpdaterInterface`, so these signatures are its contract too.

## Phases

1. **Baseline**: `phpunit` + `phpcs` green before any change.
2. **Setup**: add `rector/rector` to `require-dev` (composer.lock change; rector 2.x requires PHP ^8.2, fits project and CI), create `rector.php` with explicit type-declaration rules covering `src/` and `test/`, run `--dry-run` first.
3. **Apply Rector**: review the dry-run diff, especially: did it touch `attach`/`filter`/`__invoke` params, did it make `AbstractBlock::$view` non-nullable. Then apply.
4. **Manual semantic pass**: nullable properties, union/mixed returns, fluent `static` unification (`Layout::generate()` returns `$this`), magic methods, docblock-only params, the `isRemoved`/`isAllowed` bugs, `LayoutManager::setHandles()` int-cast for `new Handle($handle, $priority)`, `Wrapper::$tag` typed `string` with initializer `= self::DEFAULT_TAG` (currently uninitialized), `BlockFactory` switch `Interop\Container\ContainerInterface` to `Psr\Container\ContainerInterface`.
5. **Interface contracts**: hand-type the interfaces to match the typed implementations, update test stubs.
6. **Tests**: Rector on `test/` + manual typing of `AbstractTestCase` helpers and stub classes implementing project interfaces.
7. **Cleanup**: useless docblock removal (keep meaningful descriptions), `list($a, $b)` to `[$a, $b]` (`Layout::injectBlocks`), `array(...)` to `[...]` (`BeforeAfterComparison`), remove `@package`/`@author` boilerplate.
8. **Verify**: `phpunit`, `phpcs --standard=PSR12`, `php -l` loop over all files; commit per namespace; keep `rector.php` in the repo.

## BC impact & release

This is a major BC break for the published composer package: public param types narrow accepted values, and consumers implementing the project interfaces or subclassing `AbstractBlock`/`Layout`/listeners must match the new signatures.

- Version bump to `v5.0.0` with a migration note in `docs/integration.md`.
- Use `static` (not `self`) return types for fluent setters and on classes designed for subclassing.

## Risks

- `strict_types` coercion: loosely typed consumer call sites (e.g. string priorities) now throw TypeError; mitigated by the major bump and docs.
- Size: 332 methods; interface + class pairs per namespace and frequent test runs keep it manageable.
- Rector on PHP 8.4 while CI runs 8.2/8.3: generated code must not use 8.4-only syntax.
- Hidden dependencies: `BlockManager`, `ViewHelperGenerator`, `UpdateEvent`, `LayoutCollector` (LDT collector `getName(): string`, `getPriority(): int`, `collect(MvcEvent): static` are free since the LDT interface declares no return types).
