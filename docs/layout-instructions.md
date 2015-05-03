# Layout instructions

Currently there are 5 layout instructions:

1. [`layout`](#1-layout)
2. [`blocks`](#2-blocks)
3. [`remove_blocks`](#3-remove_blocks)
4. [`view_helpers`](#4-view_helpers)
5. [`include`](#5-include)

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
                'order' => -10
            ],
            /**
             * perform some actions on the block class/method calls
             */
            'actions'   => [
                'method' => ['param1', 'param2', 'param3'] // $block->method('param1', 'param2', 'param3');
            ]
        ]
    ]
];
````

## 3. `remove_blocks`

remove blocks from the layout structure:

````php
<?php
// layout update file e.g. application-index.php
return [
    'remove_blocks' => [
        'footer' => true // remove block with id 'footer'
    ]
];
````

## 4. `view_helpers`

Call view helpers. Add CSS or JavaScript assets, set page title etc.

Syntax:

````php
<?php
// if 'value' is a string, it will be the first parameter
// of the default method of the helper
// @see con-layout.global.php.dist for default methods
$headTitle = [
    'key' => 'value'
];

// value = array, call default method
$headLink = [
    'html5shiv' => [
        '//html5shiv.googlecode.com/svn/trunk/html5.js',
        'text/javascript',
        ['conditional' => 'lt IE 9']
    ]
];

// value = array, call specific method
$headLink = [
    'html5shiv' => [
        'prependFile' => [
            '//html5shiv.googlecode.com/svn/trunk/html5.js',
            'text/javascript',
            ['conditional' => 'lt IE 9']
        ]
    ]
];

````

````php
<?php
return [
    'view_helpers' => [
        /**
         * set/append/prepend head title
         * @see http://framework.zend.com/manual/current/en/modules/zend.view.helpers.head-title.html
         */
        'headTitle' => [
            'separator' => ['setSeparator' => ' - '],
            'default' => 'Default Title'
        ],
        /**
         * add css
         * @see http://framework.zend.com/manual/current/en/modules/zend.view.helpers.head-link.html
         */
        'headLink' => [
            'twbs' => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css',
            'main-css' => '/css/main.css',
            // prepend
            'some-lib' => ['prependStylesheet' => '/css/lib/some-lib.css']
        ],
        /**
         * add js
         * @see http://framework.zend.com/manual/current/en/modules/zend.view.helpers.head-script.html
         */
        'headScript' => [
            'twbs' => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js',
            'jquery' => ['prependFile' => 'https://code.jquery.com/jquery-2.1.4.min.js']
        ]
        /**
         * @see headScript
         * @see http://framework.zend.com/manual/current/en/modules/zend.view.helpers.inline-script.html
         */
        'inlineScript' => [
        ]
    ]
];
````

## 5. `include`

With include you can include another handle.

````php
<?php
// layout update file application-index-index.php
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

If you want to use the same layout structure as `application-index-index` in 
`product-index-view`:


````php
<?php
// layout update file product-index-view.php
return [
    'include' => [
        'application-index-index'
    ]
];
````
