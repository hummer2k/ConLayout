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
            ['setSeparator' => ' - '],
            'Default Title'
        ],
        'headLink' => [
            'twbs' => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css',
        ],
        'inlineScript' => [
            'jquery' => '//code.jquery.com/jquery-2.1.3.min.js',
            'twbs'   => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js'
        ],
        'headMeta' => [
            ['setCharset' => 'utf8']
        ]
    ],
    /**
     * add blocks to layout
     */
    'blocks' => [
        'header' => [
            'capture_to' => 'header',
            'template' => 'layout/partials/header'
        ],
        'navigation' => [
            'capture_to' => 'header::navigation',
            'template' => 'layout/partials/navigation'
        ],
        'footer' => [
            'capture_to' => 'footer',
            'template' => 'layout/partials/footer'
        ],
        'widget.current.time' => [
            'capture_to' => 'sidebarRight',
            'template' => 'widgets/current-time'
        ]
    ]
];
