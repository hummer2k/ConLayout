<?php
use ConLayout\Listener\LayoutUpdateListener;
return [
    'view_manager' => [
        'template_path_stack' => [
            './vendor/hummer2k/conlayout/sample/view'
        ]
    ],
    'con-layout' => [
        'update_listener_glob_paths' => [
            LayoutUpdateListener::AREA_GLOBAL => [
                'sample' => './vendor/hummer2k/conlayout/sample/layout/*.{xml,php}'
            ]
        ]
    ]
];
