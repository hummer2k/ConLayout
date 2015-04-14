<?php
return [
   'conlabz-core-controller-index-index' => [
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