# Layout instructions

There are four layout instructions:

1. [`blocks`](#1-blocks)
2. [`helpers`](#2-helpers)
3. [`include`](#3-include)
4. [`reference`](#4-reference)

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
`reference` instruction to change a block that an earlier file defined.

The files of an `include` instruction are merged before the file that
contains the instruction. So the including file can override the included
files.

ConLayout reads the `layout_updates` config before it reads the files from
`layout_update_paths`. So the files can override the config.

### Block defaults

The `block_defaults` option defines the default values for all blocks:

```php
return [
    'con-layout' => [
        'block_defaults' => [
            'capture_to' => 'content',
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


## 1. `blocks`

Add blocks to the layout:

````php
<?php
// layout update file e.g. default.php
return [
    'blocks' => [
        /**
         * unique block id
         * can be referenced within the layout model by
         * ConLayout\Layout\Layout::getBlock('my.unique.block.id');
         */
        'my.unique.block.id' => [
            /**
             * Can be Laminas\View\Model\ViewModel (default) or any class
             * implementing Laminas\View\Model\ModelInterface, instantiable
             * without constructor arguments
             */
            'class' => 'Application\Block\SomeWidget',
            /**
             * template only optional when already declared in block class
             */
            'template' => 'path/to/template',
            /**
             * where should this block be displayed?
             * syntax: <block-id>::<capture_to>
             * if no "::"-delimiter found it will be added as a child of root
             *
             * In this case the block will be added as a child of the block with
             * id 'footer' and captured to the 'content' variable in the footer 
             * template
             */
            'capture_to' => 'footer::content',
            /**
             * default: true
             */
            'append' => true,
            /**
             * set variables for this block that can be accessed with $this->var1,
             * $this->var2 in the template
             *
             * insertion metadata like the parent block and the sort order are
             * regular variables too, see "Sort order" below
             */
            'variables' => [
                'var1' => 'Hello',
                'var2' => 'World',
                'parent' => 'footer',
                'order' => -10,
                // insert this block
                'before' => 'some.other.block',
                // or
                'after' => 'some.other.block'
            ],
            /**
             * remove this block
             */
            'remove' => true,
            /**
             * perform some actions on the block class/method calls
             */
            'actions'   => [
                'my-action' => [
                    'method' => 'someMethod',
                    'param1' => 'Value param1',
                    'param3' => 'Value param3',
                    'param2' => 'Value param2'
                ]
            ]
        ]
    ]
];
````

````xml
<?xml version="1.0" encoding="UTF-8"?>
<page>
    <blocks>
        <!--
         * unique block id
         * can be referenced within the layout model by
         * ConLayout\Layout\Layout::getBlock('my.unique.block.id');
         -->
        <my.unique.block.id>
            <!--
             * Can be Laminas\View\Model\ViewModel (default) or any class
             * implementing Laminas\View\Model\ModelInterface, instantiable
             * without constructor arguments
            -->
            <class>Application\Block\SomeWidget</class>
            <!--
             * template only optional when already declared in block class
            -->
            <template>path/to/template</template>
            <!--
             * where should this block be displayed?
             * syntax: <block-id>::<capture_to>
             * if no "::"-delimiter found it will be added as a child of root
             *
             * In this case the block will be added as a child of the block with
             * id 'footer' and captured to the 'content' variable in the footer 
             * template
            -->
            <capture_to>footer::content</capture_to>
            <!--
             * default: true
            -->
            <append>0</append>
            <!--
             * set variables for this block that can be accessed with $this->var1,
             * $this->var2 in the template
             *
             * insertion metadata like the parent block and the sort order are
             * regular variables too, see "Sort order" below
            -->
            <variables>
                <var1>Hello</var1>
                <var2>World</var2>
                <order>-10</order>
                <!-- insert this block-->
                <before>some.other.block</before>
                <!-- or -->
                <after>some.other.block</after>
            </variables>
            <!--
             * wraps a block with another template
             *
             * if false, wrapper will be disabled: 'wrapper' => false,
             *
             * defaults: tag: 'div', 'template: 'blocks/wrapper'
            -->          
            <wrapper>
                <template>blocks/wrapper</template>
                <class>col-xs-12</class>
                <tag>div</tag>
            </wrapper>
            <!--
             * remove this block
            -->
            <remove>1</remove>
            <!--
             * perform some actions on the block class/method calls
            -->
            <actions>
                <my-action>
                    <method>someMethod</method>
                    <param1>Value param1</param1>
                    <param3>Value param3</param3>
                    <param2>Value param2</param2>
                </my-action>
            </actions>
       </my.unique.block.id>
    </blocks>
</page>
````

Note: Use XML attributes when possible. This reduces overhead:

````xml
<?xml version="1.0" encoding="UTF-8"?>
<page>
    <blocks>
        <my.unique.block.id class="Application\Block\SomeWidget" template="path/to/template">
            <actions>
                <my-action method="someMethod" param1="Value param1" param3="Value param3" param2="Value param2" />
            </actions>
        </my.unique.block.id>
    </blocks>
</page>
````

### Sort order

ConLayout renders the blocks in the order of their `order` variable. Blocks
with the same order keep the sequence of their insertion. Set the variable
via the `variables` key in the block definition.

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

- `block_id` - the unique block id
- `parent` - the parent block id
- `order` - the sort order
- `before` / `after` - sort order relative to another block
- `container` - container string or comma-separated container templates
- `wrapper` - wrapper template
- `has_parent` / `parent_block` - set during layout generation and injection
- `tag` and all `html_*` variables - inert, stored for consumer block code

This applies to every block, including plain `Laminas\View\Model\ViewModel`
instances that a controller returns through a `ViewModelResponse`:

````php
$block->setVariable('order', 10);
$order = $block->getVariable('order'); // 10, null when not set
````

Templates see the metadata as regular variables (`$this->order`, `$this->block_id`).
Treat the reserved names above as reserved: templates and block code must not
use them for their own variables.

## 2. `helpers`

Call view helpers. You can add CSS or JavaScript assets. You can also set the
page title.

````php
<?php
// - support for named arguments.
// Examples:

return [
    'helpers' => [
        'headScript' => [
            'jquery' => ['src' => '/js/jquery.js']
        ]
    ]
];
// result: $headScript->appendFile('/js/jquery.js');

return [
    'helpers' => [
        'headScript' => [
            'main' => [
                'src' => '/js/main.js', // src = argument name of method signature
                'after' => 'jquery'   // append after jquery
                'attrs' => [            // attrs = argument name of method signature
                    'conditional' => 'lt IE 9'
                ]
            ]
        ]
    ]
];
// result: $headScript->appendFile('/js/main.js', 'text/javascript', ['conditional' => 'lt IE 9']);

````

Or use XML:

````xml
<?xml version="1.0" encoding="UTF-8"?>
<layout>
    <helpers>
        <headScript>
            <main src="/js/main.js" depends="jquery" /> <!-- will be placed after jquery -->
            <jquery src="/js/jquery.js" />
        </headScript>
    </helpers>
</layout>

````

## 3. `include`

Include another handle.

````php
<?php
// layout update file application/index/index.php
return [
    'blocks' => [
        'header' => [
            // ...
        ],
        'footer' => [
            // ...
        ],
        'widget1' => [
            // ...
        ],
    ]
];
````

````xml
<?xml version="1.0" encoding="UTF-8"?>
<page>
    <blocks>
        <header />
        <footer />
        <widget1 />
    </blocks>
</page>
````

To use the same layout structure in `product/index/view` as in
`application/index/index`:


````php
<?php
// layout update file product/index/view.php
return [
    'include' => [
        'application/index/index'
    ]
];
````

````xml
<?xml version="1.0" encoding="UTF-8"?>
<page>
    <include handle="application/index/index" />
</page>
````

Tip: Define a virtual handle for reuse:

````php
<?php
// virtual handle file default/widgets.php
return [
    'blocks' => [
        'widget.forecast' => [
            'template' => 'widgets/forecast',
            'parent' => 'sidebar'
        ],
        'widget.calendar' => [
            'template' => 'widgets/calendar',
            'parent' => 'sidebar'
        ]
    ]
];
````

````xml
<?xml version="1.0" encoding="UTF-8"?>
<page>
    <blocks>
        <widget.forecast template="widgets/forecast" parent="sidebar" />
        <widget.calendar template="widgets/calendar" parent="sidebar" />
    </blocks>
</page>
````

````php
<?php
// layout update file application/index/index.php
return [
    // ...
    'include' => [
        'default/widgets'
    ]
]
````

````xml
<?xml version="1.0" encoding="UTF-8"?>
<page>
    <include handle="default/widgets" />
</page>
````

## 4. `reference`

Use `reference` to change the properties of a block.

````php
<?php
// default.php
return [
    'blocks' => [
        'root' => [
            'blocks' => [
                'sidebar' => [
                    'class' => 'container',
                    'blocks' => [
                        'widget.to.change' => [
                            'template' => 'my/widget'
                        ]                        
                    ]
                ]
            ]
        ]
    ]
];

````

Change the template of the widget without writing the nested structure again:

````php
<?php
// application/index/index.php
return [
    'reference' => [
        'widget.to.change' => [
            'template' => 'widgets/new-template'
        ]
    ]
];
````

## 5. Typed elements

Instead of arbitrary element names you can use typed elements with an `id`
attribute. ConLayout normalizes them into the same structure as the legacy
syntax, so both dialects merge identically across handles.

### `block`

````xml
<?xml version="1.0" encoding="UTF-8"?>
<page>
    <block id="header" order="1" template="app::layout/header" />
    <block id="footer" template="app::layout/footer" />
</page>
````

The `id` attribute becomes the block id, all other attributes become the block
definition. Blocks can be nested: a nested `<block>` becomes a child block and
gets its `parent` variable set automatically:

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

The insertion metadata `order`, `parent`, `before` and `after` are moved into
the block `variables` automatically. All other child elements (`<variables>`,
`<actions>`, legacy nested `<blocks>`) are preserved.

### `view_helper`

````xml
<?xml version="1.0" encoding="UTF-8"?>
<page>
    <view_helper name="headScript" id="main" src="/js/main.js" depends="jquery" />
    <view_helper name="headScript" id="jquery" src="/js/jquery.js" />
</page>
````

The `name` attribute selects the view helper, the `id` attribute is the
instruction id. All other attributes become the instruction arguments.

### `reference`

````xml
<?xml version="1.0" encoding="UTF-8"?>
<page>
    <reference id="main.content" append="true" />
</page>
````

Equivalent to the legacy container form. The legacy syntax and the typed
elements can be mixed in one file; the legacy `reference` container and the
typed `reference` element can be mixed as well.

### Merging across handles

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

Note: a missing `id` on a typed `block` or `view_helper` throws an
`InvalidArgumentException` with a descriptive message.

## 6. XSD validation

The typed dialect is validated by the XML Schema `xsd/layout.xsd` in the
package root. The schema declares no target namespace and defines the root
elements `page` and `layout` with the elements `block`, `view_helper`,
`reference` and `include`. The known block attributes are typed
(`template`, `capture_to`, `parent`, `before`, `after`, `class` as strings,
`append`, `remove` as booleans, `order` as integer). All other attributes
pass through, because `view_helper` arguments are dynamic attribute names.

Validate a layout update file with `xmllint`:

````sh
xmllint --noout --schema vendor/hummer2k/conlayout/xsd/layout.xsd \
    src/App/view/layout/default.xml
````

In PhpStorm, map the schema in Settings, Languages & Frameworks, Schemas
and DTDs: add a schema file entry with `layout.xsd`, no target namespace,
and the scope of your layout directories.

The schema intentionally rejects the legacy containers `<blocks>` and
`<helpers>`. Files still using them fail validation until they are migrated
to the typed elements (see section 5).
