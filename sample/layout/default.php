<?php
/**
 * sample layout update as php array
 */
return [
    /**
     * set the root/layout template
     */
    'layout' => 'layout/2cols-left',
    /**
     * add view helpers
     */
    'view_helpers' => [
        'headTitle' => [
            'separator' => [
                'method' => 'setSeparator',
                'args' => [' - '],
            ],
            'default' => 'Default Title'
        ],
        'headLink' => [
            'twbs' => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css',
        ],
        'inlineScript' => [
            'jquery' => '//code.jquery.com/jquery-2.1.3.min.js',
            'twbs'   => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js'
        ],
        'headMeta' => [
            'charset' => [
                'method' => 'setCharset',
                'args' => ['utf8']
            ]
        ]
    ],
    /**
     * add blocks to layout
     */
    'blocks' => [
        'head' => [
            'capture_to' => 'head',
            'template' => 'layout/partials/head',
            'options' => [
                'order' => 10000
            ]
        ],
        'header' => [
            'capture_to' => 'header',
            'template' => 'layout/partials/header'
        ],
        'navbar.brand' => [
            'capture_to' => 'header::navbar',
            'template'   => 'layout/partials/navbar/brand',
            'options' => [
                'after' => 'navbar.toggle'
            ]
        ],
        'navbar.toggle' => [
            'capture_to' => 'header::navbar',
            'template' => 'layout/partials/navbar/toggle',
        ],
        'navigation' => [
            'capture_to' => 'header::navigation',
            'template' => 'layout/partials/navigation'
        ],
        'footer' => [
            'capture_to' => 'footer',
            'template' => 'layout/partials/footer'
        ]
    ]
];
