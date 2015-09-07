<?php
return [
    'layout' => 'layout/3cols',
    'blocks' => [
        'navbar.brand' => [
            'variables' => [
                'brand' => 'Application/Index/View'
            ]
        ],
        'widget.current.time' => [
            'capture_to' => 'sidebarSecondary',
            'template'   => 'widgets/current-time'
        ]
    ]
];
