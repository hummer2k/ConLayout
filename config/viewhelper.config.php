<?php

use ConLayout\View\Helper\Block;
use ConLayout\View\Helper\BlockFactory;
use ConLayout\View\Helper\BodyClass;
use ConLayout\View\Helper\Layout;
use ConLayout\View\Helper\LayoutFactory;
use ConLayout\View\Helper\Proxy\ViewHelperProxyAbstractFactory;
use ConLayout\View\Helper\Wrapper;

return [
    'invokables' => [
        Wrapper::class   => Wrapper::class,
        BodyClass::class => BodyClass::class
    ],
    'factories' => [
        Block::class  => BlockFactory::class,
        Layout::class => LayoutFactory::class
    ],
    'aliases' => [
        'bodyClass' => BodyClass::class,
        'block'     => Block::class,
        'layout'    => Layout::class,
        'wrapper'   => Wrapper::class
    ],
    'abstract_factories' => [
        ViewHelperProxyAbstractFactory::class
    ]
];
