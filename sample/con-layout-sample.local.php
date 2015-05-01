<?php
use ConLayout\Listener\LayoutUpdateListener;
return [
    'view_manager' => [
        'template_path_stack' => [
            './vendor/hummer2k/conlayout/sample/view'
        ]
    ],
    'con-layout' => [
        'layout_update_paths' => [
            LayoutUpdateListener::AREA_GLOBAL => [
                './vendor/hummer2k/conlayout/sample/layout'
            ]
        ],
        'layout_update_extensions' => [
            'xml',
            'php'
        ]
    ]
];
