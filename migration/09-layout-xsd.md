# Migration: XSD for the typed layout XML format

## Scope & baseline

- `08-typed-xml-elements.md` is applied. Layout XML supports the typed dialect:
  `<block id="..." />`, `<view_helper name="..." id="..." />`, `<reference id="..." />`,
  normalized into the keyed structure by `ConLayout\Updater\Collector\XmlPreprocessor`.
  The legacy containers `<blocks>`, `<helpers>` still parse, but no sandbox file uses
  them anymore.
- This migration adds `xsd/layout.xsd`, an XML Schema (XSD 1.0) that validates the
  typed dialect. It is strict: the top level accepts exactly `block`, `view_helper`,
  `reference` and `include`. Legacy containers are rejected intentionally.
- No runtime code changes. The schema is documentation and tooling support (IDE
  validation, CI checks) only.

## Desired result

`vendor/hummer2k/conlayout/xsd/layout.xsd` validates every layout XML file in the
sandbox and every typed fixture in the package test suite:

```sh
xmllint --noout --schema vendor/hummer2k/conlayout/xsd/layout.xsd \
  src/App/view/layout/default.xml
```

## Decisions

1. **No target namespace.** The layout XML files carry no `xmlns`, so the schema
   declares no `targetNamespace`. Global root elements are `page` and `layout`
   (both appear in the docs), sharing one `layoutUpdateType`.
2. **Strict top level.** `layoutUpdateType` is an `xs:choice` of exactly `block`,
   `view_helper`, `reference`, `include`. A legacy `<blocks>` or `<helpers>`
   container fails validation. This enforces the new format; migrating remaining
   legacy files is out of scope (documented in `08-typed-xml-elements.md`).
3. **Explicit known attributes plus `xs:anyAttribute processContents="lax"`.**
   The known block attributes are typed (`template`, `capture_to`, `parent`,
   `before`, `after`, `class` as `xs:string`, `append`, `remove` as `xs:boolean`,
   `order` as `xs:integer`). The wildcard keeps the dialect extensible, which is
   required for `view_helper`: its arguments are dynamic attribute names (e.g.
   `value="Products"`) and cannot be enumerated.
4. **Block content mirrors the preprocessor.** A `block` (and `reference`) may
   contain nested `block` elements (recursion), `variables`, `actions`, `wrapper`
   (each with an inner `xs:any processContents="lax"`), plus a final `xs:any lax`
   catch-all so legacy child elements like `<template>` or `<capture_to>` inside a
   typed block stay expressible.
5. **`include` stays valid.** `handle` attribute (`xs:string`), content
   `xs:any lax` so legacy multi-handle child elements still pass.
6. **Validation test via `DOMDocument::schemaValidate()`.** `ext-dom` is part of
   every supported PHP build, so the test runs everywhere the suite runs.

## Implementation

1. `xsd/layout.xsd` - the schema as described above.
2. `test/Xsd/LayoutXsdTest.php` - `ConLayoutTest\Xsd\LayoutXsdTest`, resolves
   `xsd/layout.xsd` relative to the test file and validates all typed fixtures.

## Tests

- `test/Xsd/LayoutXsdTest.php` (integration): every file under
  `test/Updater/Collector/_files/typed/` validates against the XSD. One negative
  test loads a minimal legacy XML string (`<blocks><header/></blocks>`) and
  expects `schemaValidate()` to fail.

## Documentation

New section "## 6. XSD validation" in `docs/layout-instructions.md`: schema
location, the `xmllint` invocation, a PhpStorm hint (Settings, Languages &
Frameworks, Schemas and DTDs: map `layout.xsd` with no target namespace), and the
note that legacy containers are rejected.

## Verification

- From `vendor/hummer2k/conlayout/`: `./vendor/bin/phpunit` (whole suite green).
- `composer cs-check` (php-cs-fixer, PER Coding Style).
- `xmllint --noout --schema` against the schema for all sandbox files
  (`src/App/view/layout/**`, `src/Catalog/view/layout/**` in the mezzio app) and
  all typed fixtures. No existing XML file is modified.

## Commit plan

1. `feat(layout): add XSD for the typed layout XML dialect`.

## Risks & open points

- **Legacy containers are rejected.** Any file still using `<blocks>` or
  `<helpers>` fails validation until migrated to typed elements. Accepted;
  documented in the schema section.
- **Root element name is fixed to `page`/`layout`.** A different root name fails
  validation. Accepted; all current files use `<page>`.
- **`xs:anyAttribute lax` does not catch attribute typos.** Only the known core
  attributes are typed. This is the price of keeping `view_helper` arguments and
  future attributes working without schema updates.
- **No `noNamespaceSchemaLocation` in the XML files.** The hint would couple the
  files to a local path; IDE mapping is left to the user (documented).
