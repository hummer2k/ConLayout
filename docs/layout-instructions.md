# Layout instructions

Currently there are 4 layout instructions:

1. [`layout`](#1-layout)
2. [`blocks`](#2-blocks)
3. [`view_helpers`](#4-view_helpers)
4. [`include`](#5-include)

## 1. `layout`

sets the layout template:

````php
<?php
// layout update file e.g. default.php
return [
    'layout' => 'layout/2cols-left'
];
````

## 2. `blocks`

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
             * wraps a block with another template
             *
             * if false, wrapper will be disabled: 'wrapper' => false,
             *
             * defaults: tag: 'div', 'template: 'blocks/wrapper'
             */             
            'wrapper' => [
                'template' => 'blocks/wrapper',
                'class' => 'col-xs-12',
                'tag' => 'div'
            ],
            /**
             * remove this block
             */
            'remove' => true,
            // just set a custom template
            'wrapper' => 'my/wrapper',
            // or add more attributes for the wrapper tag
            'wrapper' => [
                'id' => 'some-id',
                'title' => 'Wrapper Title'
            ],
            /**
             * perform some actions on the block class/method calls
             */
            'actions'   => [
                'my-action' => [
                    'method' => ['param1', 'param2', 'param3'] // $block->method('param1', 'param2', 'param3');
                ]
            ]
        ]
    ]
];
````

## 3. `remove_blocks`

remove blocks from the layout structure:

````php
<?php
// layout update file e.g. application/index.php
return [
    'remove_blocks' => [
        'footer' => true // remove block with id 'footer'
    ]
];
````

## 4. `view_helpers`

Call view helpers. Add CSS or JavaScript assets, set page title etc.

````php
<?php
// - support for named arguments.
// - __call() methods are implemented by a proxy (ConLayout\View\Helper\Proxy)
// Examples:

return [
    'view_helpers' => [
        'headScript' => [
            'jquery' => '/js/jquery.js'
        ]
    ]
];
// result: $headScript->appendFile('/js/jquery.js');

return [
    'view_helpers' => [
        'headScript' => [
            'main' => [
                'src' => '/js/main.js', // src = argument name of method signature
                'depends' => 'jquery'   // append after jquery
                'attrs' => [            // attrs = argument name of method signature
                    'conditional' => 'lt IE 9'
                ]
            ]
        ]
    ]
];

````

or via XML:

````xml
<?xml version="1.0" encoding="UTF-8"?>
<layout>
    <view_helpers>
        <headScript>
            <main src="/js/main.js" depends="jquery" /> <!-- will be placed after jquery -->
            <jquery src="/js/jquery.js" />
        </headScript>
    </view_helpers>
</layout>

````

## 5. `include`

Includes another handles

````php
<?php
// layout update file application/index/index.php
return [
    'layout' => '3cols',
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

If you want to use the same layout structure as `application/index/index` in 
`product/index/view`:


````php
<?php
// layout update file product/index/view.php
return [
    'include' => [
        'application/index/index' => true
    ]
];
````

You can disable the include within another handle:

````php
<?php
// layout update file product/index/view.php
return [
    'include' => [
        'application/index/index' => false
    ]
];
````

Tipp: Define a "virtual handle" for reuse:

````php
<?php
// virtual handle file default/widgets.php
return [
    'blocks' => [
        'widget.forecast' => [
            'template' => 'widgets/forecast',
            'capture_to' => 'sidebar'
        ],
        'widget.calendar' => [
            'template' => 'widgets/calendar',
            'capture_to' => 'sidebar'
        ]
    ]
];
````

````php
<?php
// layout update file application/index/index.php
return [
    // ...
    'include' => [
        'default/widgets' => true
    ]
]
````
