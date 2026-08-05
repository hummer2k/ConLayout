<?php

use ConLayout\View\Helper\Block;
use ConLayout\View\Helper\BlockFactory;
use ConLayout\View\Helper\BodyClass;
use ConLayout\View\Helper\Layout;
use ConLayout\View\Helper\LayoutFactory;
use ConLayout\View\Helper\Wrapper;
use ConLayout\View\Helper\WrapperFactory;

return [
    'invokables' => [
        BodyClass::class => BodyClass::class
    ],
    'factories' => [
        Block::class  => BlockFactory::class,
        Layout::class => LayoutFactory::class,
        Wrapper::class => WrapperFactory::class
    ],
    'aliases' => [
        'bodyClass' => BodyClass::class,
        'block'     => Block::class,
        'layout'    => Layout::class,
        'wrapper'   => Wrapper::class
    ]
];
