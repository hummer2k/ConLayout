# Layout instructions

Layout updates are XML files. Each file uses typed elements inside a `page`
or `layout` root element. The XML Schema `xsd/layout.xsd` validates the
dialect, see [XSD validation](#xsd-validation).

## How ConLayout finds the layout updates

ConLayout uses handles to select the layout update files. Each handle has a
name and a priority.

The `default` handle always exists. Its priority is `-1`.

For a successful route, ConLayout creates one handle for each part of the
route name. The route name `app.index.index` creates these handles:

- `app` with priority `10`
- `app/index` with priority `20`
- `app/index/index` with priority `30`

ConLayout converts route names to lowercase. It keeps the dot separators.
For example, the route name `App.Index` creates the handles `app` and
`app/index`.

For a route that matches nothing, ConLayout adds the handle
`error-router-no-match` with priority `666`. For a handled exception, it
adds the handle `error-<exception-name>` with priority `666`. For example,
a `NotFoundException` creates the handle `error-not-found-exception`.

ConLayout processes the handles in the order of their priority. The lowest
priority comes first. The file name for a handle is the handle name plus a
file extension, for example `default.xml` or `app/index.xml`. ConLayout
searches the file in the `global` area and in the current area. For the
search paths, see `docs/areas.md`.

You can add and remove handles in your code. Use `addHandle()` and
`removeHandle()` on the `LayoutManager`. See `docs/controller-plugin.md`.

### Merge order

The layout update files are merged in the order of the handle priorities.
A file of a later handle can override a file of an earlier handle. Use the
`reference` element to change a block that an earlier file defined.

The files of an `include` element are merged before the file that contains
the element. So the including file can override the included files.

ConLayout reads the `layout_updates` config before it reads the files from
`layout_update_paths`. So the files can override the config.

### Block defaults

The `block_defaults` option defines the default values for all blocks:

```php
return [
    'con-layout' => [
        'block_defaults' => [
            'captureTo' => 'content',
            'append'     => true,
            'class'      => Laminas\View\Model\ViewModel::class,
            'variables'  => [],
            'template'   => '',
            'actions'    => [],
        ],
    ],
];
```

You can override the defaults for a single block in the block definition.

## `block`

Add blocks to the layout:

````xml
<?xml version="1.0" encoding="UTF-8"?>
<page>
    <block id="header" template="app::layout/header" />
    <block id="footer" template="app::layout/footer" />
</page>
````

The `id` attribute is required and becomes the block id. All other
attributes become the block definition:

- `class` - the block view model class. Can be
  `Laminas\View\Model\ViewModel` (default) or any class implementing
  `Laminas\View\Model\ModelInterface`, instantiable without constructor
  arguments.
- `template` - the template name; optional when already declared in the
  block class.
- `captureTo` - where should this block be displayed? Syntax:
  `<block-id>::<captureTo>`. If no `::` delimiter is found, the block is
  added as a child of `root`. For example, `captureTo="footer::content"`
  adds the block as a child of the block with id `footer` and captures it
  to the `content` variable in the footer template.
- `append` - default `true`.
- `remove` - remove a block that an earlier file defined.
- `order`, `parentId`, `before`, `after` - insertion metadata. ConLayout
  moves them into the block `variables` automatically, see
  [Sort order](#sort-order).
- any other attribute passes through as a block variable.

### Children

Blocks can be nested: a nested `<block>` becomes a child block and gets its
`parentId` variable set automatically:

````xml
<?xml version="1.0" encoding="UTF-8"?>
<page>
    <block id="root" template="base">
        <block id="header" template="app::layout/header">
            <block id="main.nav" template="app::layout/navigation" />
        </block>
        <block id="main.content" />
    </block>
</page>
````

- `<variables>` - set variables for this block that can be accessed with
  `$this->var1`, `$this->var2` in the template. Insertion metadata like the
  parent block and the sort order are regular variables too, see
  [Sort order](#sort-order) below.

  ````xml
  <variables>
      <var1>Hello</var1>
      <var2>World</var2>
  </variables>
  ````

- `<actions>` - perform some actions on the block class/method calls:

  ````xml
  <actions>
      <my-action method="someMethod" param1="Value param1" param3="Value param3" param2="Value param2" />
  </actions>
  ````

- `<wrapper>` - wraps a block with another template. Use `wrapper="false"`
  to disable the wrapper. Defaults: `tag` `div`, `template`
  `blocks/wrapper`.

  ````xml
  <wrapper>
      <template>blocks/wrapper</template>
      <class>col-xs-12</class>
      <tag>div</tag>
  </wrapper>
  ````

### Sort order

ConLayout renders the blocks in the order of their `order` variable. Blocks
with the same order keep the sequence of their insertion. Set the variable
via the `order` attribute or the `variables` child element.

The sort order only counts inside the parent block. ConLayout compares the
`order` values of sibling blocks with the same parent. The order values of
blocks in different parents do not influence each other.

You can also use the `before` and `after` variables. ConLayout converts them
to an `order` value at sort time:

- `before` gives the block the order of the target block minus `1`.
- `after` gives the block the order of the target block plus `1`.

The target block must exist in the block pool and in the same parent. The
variables do not work when you reference a block that ConLayout removed or a
block in a different parent.

### Internal block metadata

A layout update defines how a block is inserted and rendered through regular
variables. Set them with the `variables` key in a layout update or with
`setVariable()` in your code. The following variable names are reserved and
must not be used by block code or templates for their own data:

- `blockId` - the unique block id
- `parentId` - the parent block id
- `order` - the sort order
- `before` / `after` - sort order relative to another block
- `container` - container string or comma-separated container templates
- `wrapper` - wrapper template
- `hasParent` / `parent` - set during layout generation and injection
- `tag` and all `html_*` variables - inert, stored for consumer block code

This applies to every block, including plain `Laminas\View\Model\ViewModel`
instances that a controller returns through a `ViewModelResponse`:

````php
$block->setVariable('order', 10);
$order = $block->getVariable('order'); // 10, null when not set
````

Templates see the metadata as regular variables (`$this->order`, `$this->blockId`).
Treat the reserved names above as reserved: templates and block code must not
use them for their own variables.

## `css`, `js`, `title`

Sugar elements for the most common view helpers. They normalize into the
`helpers` instruction structure, so sorting (`before`, `after`, `depends`),
`remove="true"` and the debug `data-layout-id` work exactly the same.

````xml
<?xml version="1.0" encoding="UTF-8"?>
<page>
    <css id="all.css" src="/css/all.css" />
    <js id="main.js" src="/js/main.js" depends="jquery.js" />
    <js id="inline.js" src="/js/inline.js" inline="true" />
    <title id="products" append="true">Products</title>
</page>
````

- `<css>` maps to the `headLink` helper; `src` is passed as `href`, an
  explicit `href` attribute passes through untouched.
- `<js>` maps to the `headScript` helper; with `inline="true"` it maps to
  `inlineScript` instead.
- `<title>` maps to the `headTitle` helper. The element content becomes the
  title; at most one of `append`, `set` (overrides) or `prepend` selects the
  helper method, and without any of them the title is appended:

````xml
<title id="default">TEST</title>
<title id="site" set="true">My Shop</title>
<title id="home" prepend="true">Home</title>
````

All three require the `id` attribute like `block`.

## `reference`

Use `reference` to change the properties of a block:

````xml
<?xml version="1.0" encoding="UTF-8"?>
<page>
    <reference id="main.content" append="true" />
</page>
````

The `id` attribute is required and selects the block. The insertion metadata
`order`, `parentId`, `before` and `after` are moved into the block
`variables` automatically, like for `block`.

## `include`

Include another handle:

````xml
<?xml version="1.0" encoding="UTF-8"?>
<page>
    <include handle="application/index/index" />
</page>
````

The included handle is merged before the file that contains the element. So
the including file can override the included files.

Tip: define a virtual handle for reuse:

````xml
<?xml version="1.0" encoding="UTF-8"?>
<page>
    <block id="widget.forecast" template="widgets/forecast" parentId="sidebar" />
    <block id="widget.calendar" template="widgets/calendar" parentId="sidebar" />
</page>
````

````xml
<?xml version="1.0" encoding="UTF-8"?>
<page>
    <include handle="default/widgets" />
</page>
````

## Merging across handles

`default.xml`:

````xml
<?xml version="1.0" encoding="UTF-8"?>
<page>
    <block id="header" order="1" template="app::layout/header" />
</page>
````

`app/checkout.xml` (higher handle priority):

````xml
<?xml version="1.0" encoding="UTF-8"?>
<page>
    <block id="header" template="app::layout/header-minimal" />
</page>
````

Result:

````php
[
    'blocks' => [
        'header' => [
            'template'  => 'app::layout/header-minimal',
            'variables' => ['order' => '1'],
        ],
    ],
]
````

Note: a missing `id` on a typed element throws an
`InvalidArgumentException` with a descriptive message.

## Custom elements

Each typed element is normalized by a class implementing
`ConLayout\Updater\Collector\Element\ElementInterface`, collected by the
`XmlPreprocessor`:

- `name()` returns the XML element name,
- `instruction()` returns the instruction key the element is merged into
  (`blocks`, `helpers` or `reference`),
- `normalize($spec)` returns the fragment to merge, keyed like the
  instruction structure.

To add a custom element, implement the interface (optionally extending
`AbstractElement` for the shared attribute validation and list handling) and
register it in the `con-layout.xml_preprocessor.elements` config:

````php
use ConLayout\Updater\Collector\Element\AbstractElement;
use ConLayout\Generator\ViewHelperGenerator;

class MetaElement extends AbstractElement
{
    public const NAME = 'meta';

    public function name(): string
    {
        return self::NAME;
    }

    public function instruction(): string
    {
        return ViewHelperGenerator::INSTRUCTION;
    }

    public function normalize(array $spec): array
    {
        $id = $this->requireString($spec, self::ATTRIBUTE_ID, self::NAME);
        unset($spec[self::ATTRIBUTE_ID]);
        return ['headMeta' => [$id => $spec]];
    }
}
````

````php
// config/con-layout.global.php
'xml_preprocessor' => [
    'elements' => [
        'meta' => MetaElement::class,
        // built-in elements omitted for brevity
    ],
],
````

The element class must be registered as a service in the dependency
container. Every element in the list is applied by the preprocessor in
config order.

## XSD validation

The typed dialect is validated by the XML Schema `xsd/layout.xsd` in the
package root. The schema declares no target namespace and defines the root
elements `page` and `layout` with the elements `block`, `css`, `js`, `title`,
`reference` and `include`. The known block attributes are typed
(`template`, `captureTo`, `parentId`, `before`, `after`, `class` as strings,
`append`, `remove` as booleans, `order` as integer). All other attributes
pass through, because helper instruction arguments are dynamic attribute
names.

Validate a layout update file with `xmllint`:

````sh
xmllint --noout --schema vendor/hummer2k/conlayout/xsd/layout.xsd \
    src/App/view/layout/default.xml
````

In PhpStorm, map the schema in Settings, Languages & Frameworks, Schemas
and DTDs: add a schema file entry with `layout.xsd`, no target namespace,
and the scope of your layout directories.

The schema intentionally rejects the legacy containers `<blocks>` and
`<helpers>` as well as the container form of `reference`. Layout update
files must use the typed elements.
