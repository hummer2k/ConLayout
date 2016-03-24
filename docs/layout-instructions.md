# Layout instructions

Currently there are 4 layout instructions:

1. [`blocks`](#1-blocks)
2. [`helpers`](#2-helpers)
3. [`include`](#3-include)
4. [`reference`](#4-reference)


## 1. `blocks`

add blocks to layout:

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
             * Can extend from ConLayout\Block\AbstractBlock
             * Should implement ConLayout\Block\BlockInterface
             * optional, defaults to Zend\View\ViewModel
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
             * Can extend from ConLayout\Block\AbstractBlock
             * Should implement ConLayout\Block\BlockInterface
             * optional, defaults to Zend\View\ViewModel
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

Note: Use XML attributes when possible to reduce overhead:

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

## 2. `helpers`

Call view helpers. Add CSS or JavaScript assets, set page title etc.

````php
<?php
// - support for named arguments.
// - __call() methods are implemented by a proxy (ConLayout\View\Helper\Proxy)
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

or via XML:

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

Includes another handle

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

If you want to use the same layout structure as `application/index/index` in 
`product/index/view`:


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

Tip: Define a "virtual handle" for reuse:

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

Reference a block to change its properties.

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
