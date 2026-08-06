# Migration: typed XML elements for layout updates

## Scope & baseline

- `07-language-modernization.md` is applied. XML layout updates are parsed with `Laminas\Config\Factory::fromFile` (`Laminas\Config\Reader\Xml`), block ids and view-helper call ids are element names (`<blocks><header .../></blocks>`, `<helpers><headScript><main .../></headScript></helpers>`). The keys become the `blocks`/`helpers` structure that `LayoutUpdater::fetchHandle()` deep-merges per handle via `Laminas\Config\Config::merge`.
- This migration adds a typed dialect: `<block id="..." />`, `<view_helper name="..." id="..." />` and `<reference id="..." />`. The ids move from element names into `id`/`name` attributes, so a later handle can override a block defined by an earlier handle without repeating the arbitrary element name.
- The transformation is a normalization step inside `FilesystemCollector` (the place where XML files enter), producing the exact keyed structure the legacy XML produces today. Everything downstream (`Config::merge`, `BlocksGenerator`, `ViewHelperGenerator`, `BlockFactory`) stays untouched.
- Out of scope: typed elements in PHP layout updates, a separate XML reader, XSD validation, nested `<block>` inside `<block>` as a new nesting mechanism (nesting stays via `<blocks>` children, `capture_to` or the `parent` variable).

## Desired result

`default.xml`:

```xml
<page>
    <block id="header" order="1" template="app::layout/header" />
</page>
```

`app/checkout.xml` (higher handle priority):

```xml
<page>
    <block id="header" template="app::layout/header-minimal" />
</page>
```

Merged by `LayoutUpdater`:

```php
blocks.header = [
    'template'  => 'app::layout/header-minimal',
    'variables' => ['order' => '1'],
];
```

## Decisions

1. **Array-level normalizer instead of a string/DOM preprocessor.** The alternative (rewriting the XML string via `DOMDocument` before parsing) means re-serializing XML with namespaces, entities, CDATA and `xi:include` edge cases. Normalizing the parsed `Config` is pure, unit-testable and keeps the Laminas reader as the single parsing authority.
2. **New class `ConLayout\Updater\Collector\XmlPreprocessor`.** Stateless `final class` with `process(Config $config): Config`. It never mutates the input and returns a new `Config` (the file configs may be read-only).
3. **Typed `<block>`.** `id` attribute is required and becomes the key under `blocks`; `id` is dropped from the specs. Single and multiple elements (assoc vs sequential list from the reader) are both handled. **Nested typed `<block>` elements recurse**: the reader emits a nested `block` key, which is normalized into a `blocks` child of the parent spec, so `<block id="root">` can contain `<block id="header">` children directly. `BlocksGenerator::generateBlocks()` already consumes this recursively and sets the `parent`/`has_parent` variables. Legacy `<blocks>` children in the same spec are merged with the nested typed blocks, so `<block id="root">...<blocks>...</blocks></block>` composes with the existing nesting too. `view_helper` and `reference` stay top-level only.
4. **Insertion metadata moves into `variables`.** `order`, `parent`, `before`, `after` are read from block variables by `BlockRegistry::sort()`/`getParentId()`; `BlockFactory::configure()` only reads `variables`, `actions`, `template`, `capture_to`, `append`. So typed blocks (and typed references) map these attributes into `variables` automatically. Without this, `<block id="header" order="1" />` would silently not sort.
5. **Typed `<view_helper>`.** `name` and `id` attributes are required and dropped; all other attributes become the instruction spec: `<view_helper name="headScript" id="main" src="/js/main.js" depends="jquery" />` maps to `helpers.headScript.main`. Sorter keys like `depends` pass through untouched.
6. **Typed `<reference>`.** `<reference id="main.content" append="true" />` maps to `reference.main.content`, with the same `variables` mapping as blocks (references are merged into block specs by `BlocksGenerator` before `configure()` runs). The legacy container form `<reference><main.content .../></reference>` is detected per entry (no string `id` key) and merged as-is, so both forms can coexist in one file. A legacy block literally named `id` inside a reference is unsupported.
7. **Idempotent and always on.** No typed keys present means the input is returned unchanged; legacy XML files are unaffected. PHP layout updates skip the normalizer entirely (the collector checks for `.xml`), because a PHP file can legitimately contain a top-level `reference` key.
8. **Missing required attributes throw.** `ConLayout\Exception\InvalidArgumentException` (new, implements `ExceptionInterface`), fail fast with a clear message.
9. **Service wiring.** `XmlPreprocessor` is stateless and registered via `Laminas\ServiceManager\Factory\InvokableFactory`; `FilesystemCollectorFactory` passes it as a new optional third constructor argument of `FilesystemCollector` (default `null` keeps direct instantiation in existing tests working).

## Implementation

1. `src/Updater/Collector/XmlPreprocessor.php` - the normalizer as described above.
2. `src/Exception/InvalidArgumentException.php` - new package exception.
3. `src/Updater/Collector/FilesystemCollector.php` - optional `?XmlPreprocessor` constructor arg; in `collect()` apply it to `.xml` files before merging into `$tempStructure`.
4. `src/Updater/Collector/FilesystemCollectorFactory.php` - pass `$container->get(XmlPreprocessor::class)`.
5. `config/service.config.php` - register `XmlPreprocessor::class` with `InvokableFactory`.

## Tests

- `test/Updater/Collector/XmlPreprocessorTest.php` (unit): single and multiple typed blocks, recursive nested typed blocks, `id` dropped, metadata moved to `variables`, nested children preserved, mixed legacy `<blocks>` + typed `<block>` in one file, typed `view_helper`, typed and legacy `reference` coexistence, no-op on legacy input, missing `id`/`name` throws.
- `test/Updater/Collector/FilesystemCollectorTest.php`: new fixture directories (separate from the existing `_files/global|frontend`) with typed XML; the collector with the preprocessor collects and normalizes.
- `test/Updater/LayoutUpdaterTest.php`: integration test with a real `FilesystemCollector` + `XmlPreprocessor` and two handles (`default` and a higher-priority `app/checkout` fixture) proving the exact merge result above.

## Documentation

New section in `docs/layout-instructions.md`: dialect table for the three typed elements, the `default.xml` / `app/checkout.xml` merge example, the `variables` auto-mapping, and that the legacy syntax remains fully supported.

## Verification

- From `vendor/hummer2k/conlayout/`: `./vendor/bin/phpunit` (whole suite green).
- `composer cs-fix` (php-cs-fixer, PER Coding Style).
- Manual check in the mezzio sandbox: convert `src/App/view/layout/default.xml` to the typed dialect and verify the rendered page and the debug toolbar.

## Commit plan

1. `feat(updater): support typed block/view_helper/reference XML elements`.

## Risks & open points

- **Ambiguity of a legacy reference block literally named `id`.** `<reference><id .../></reference>` would be misdetected as typed. Accepted as pathological; documented.
- **Mixing typed `reference` with legacy `reference` containers in one file.** Supported per entry; if an entry carries a string `id` attribute it wins and is treated as typed.
- **String values from XML attributes.** `order="1"` stays a string in `variables` until it is compared by `BlockRegistry::sort()` (`<=>`). Legacy XML has the same behavior for `<variables><order>-10</order></variables>`.
