<?php

use ConLayout\Updater\LayoutUpdaterInterface;

return [
    LayoutUpdaterInterface::INSTRUCTION_BLOCKS => [
        'widget.1' => [
            'capture_to' => 'sidebarLeft',
            'options' => [
                'order' => 10
            ]
        ],
        'breadcrumbs' => [
            'capture_to' => 'content'
        ],
        'widget.1.child' => [
            'capture_to' => 'widget.1::childHtml'
        ],
        'some.removed.block' => [
            'remove' => true
        ]
    ]
];
