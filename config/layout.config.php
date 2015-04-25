<?php
return [
    'conlabz-core-controller-index-index' => [
        'view_helpers' => [
            'headLink' => [
                'another-css' => false,
                'some-id' => [
                    'prependStylesheet' => '/css/lorem/ipsum.css'
                ]
            ],
            'headMeta' => [
                'charset' => [
                    'setCharset' => 'utf-8'
                ]
            ],
            'headScript' => [
                'jquery-1.11.2' => [
                    'prependFile' => '//code.jquery.com/jquery-1.11.2.min.js',
                ]
            ]
        ],
        'blocks' => [
            'widget.before.content' => [
                'capture_to' => 'anonymous.content.1::childHtml',
            ]
        ],
    ],
    'default' => [
        'layout' => '2cols-left',
        'view_helpers' => [
            'headTitle' => [
                'MY TITLE!'
            ],
            'headLink' => [
                'main-css' => '/css/main.css',
                'another-css' => '/css/another.css',
                'con-layout-debug' => '/css/con-layout.css',
            ]
        ],
        'blocks' => [
            'head' => [
                'capture_to' => 'head',
                'template' => 'layout/head'
            ],
            'header' => [
                'capture_to' => 'header',
                'template' => 'layout/header'
            ],
            'navigation' => [
                'capture_to' => 'header::navigation',
                'template' => 'layout/navigation'
            ],
            'footer' => [
                'capture_to' => 'footer',
                'template' => 'layout/footer'
            ],
            'my.widget' => [
                'capture_to' => 'root::sidebarLeft',
                'template' => 'sidebar-test',
                'variables' => [
                    'title' => 'Pos 2',
                    'content' => 'Content'
                ]
            ],
            'widget.before' => [
                'capture_to' => 'sidebarLeft',
                'template' => 'sidebar-test',
                'variables' => [
                    'title' => 'Pos 1',
                ],
                'options' => [
                    'order' => -1
                ]
            ],
            'widget.before.content' => [
                'capture_to' => 'widget.before::childHtml',
                'template' => 'widget-content'
            ]
        ]
    ]
];