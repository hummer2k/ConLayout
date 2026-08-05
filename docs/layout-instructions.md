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
            'options'    => [],
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
            'parent' => 'footer',
            /**
             * default: true
             */
            'append' => true,
            /**
             * set variables for this block that can be accessed with $this->var1,
             * $this->var2 in the template
             */
            'variables' => [
                'var1' => 'Hello',
                'var2' => 'World'
            ],
            /**
             * add options, for example to define the sort order
             */
            'options' => [
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
            -->
            <variables>
                <var1>Hello</var1>
                <var2>World</var2>
            </variables>
            <!--
             * add options, for example to define the sort order
            -->
            <options>
                <order>-10</order>
                <!-- insert this block-->
                <before>some.other.block</before>
                <!-- or -->
                <after>some.other.block</after>
            </options>
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

ConLayout renders the blocks in the order of their `order` option. Blocks
with the same order keep the sequence of their insertion.

You can also use the `before` and `after` options. ConLayout converts them
to an `order` value at sort time:

- `before` gives the block the order of the target block minus `1`.
- `after` gives the block the order of the target block plus `1`.

The target block must exist in the block pool. The options do not work
when you reference a block that ConLayout removed.

### Internal block metadata

The `options` key in a layout update defines how a block is inserted and
rendered. Internally ConLayout stores each option as its own variable on the
view model. The following variable names are reserved and must not be used
by block code or templates for their own data:

- `block_id` - the unique block id
- `parent` - the parent block id
- `order` - the sort order
- `before` / `after` - sort order relative to another block
- `container` - container string or comma-separated container templates
- `wrapper` - wrapper template
- `has_parent` / `parent_block` - set during layout generation and injection
- `tag` and all `html_*` options - inert, stored for consumer block code

This applies to every block, including plain `Laminas\View\Model\ViewModel`
instances that a controller returns through a `ViewModelResponse`. The
layout-XML-facing `options:` key is unchanged; only the internal storage on
the model changed:

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
