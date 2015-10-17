<?php
return [
    'invokables' => [
        'ConLayout\View\Helper\Wrapper' => 'ConLayout\View\Helper\Wrapper',
        'ConLayout\View\Helper\BodyClass' => 'ConLayout\View\Helper\BodyClass',
    ],
    'factories' => [
        'ConLayout\View\Helper\Block' => 'ConLayout\View\Helper\BlockFactory'
    ],
    'aliases' => [
        'bodyClass' => 'ConLayout\View\Helper\BodyClass',
        'block' => 'ConLayout\View\Helper\Block',
        'wrapper' => 'ConLayout\View\Helper\Wrapper'
    ],
    'abstract_factories' => [
        'ConLayout\View\Helper\Proxy\ViewHelperProxyAbstractFactory'
    ]
];
