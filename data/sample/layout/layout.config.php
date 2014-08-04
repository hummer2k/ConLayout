<?php

return [
    'default' => [        
        'layout' => '2cols-left',
        'headLink' => [
            'assets/css/bootstrap.css',
            '//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css'
        ],
        'headScript' => [
            'js/jquery.min.js',
            'assets/js/bootstrap.js',
            ['js/html5.js', 'text/javascript', ['conditional' => 'lt IE 9']],            
        ],
        'headMeta' => [
            'setCharset' => 'utf-8',
            ['viewport', 'width=device-width, initial-scale=1.0']
        ],
        'headTitle' => [
            'My Default Title'
        ],
        'blocks' => [
            'head' => [
                'head' => [
                    'template' => 'layout/head'
                ]
            ],
            'header' => [
                'header' => [
                    'template' => 'layout/header',
                    'children' => [
                        'navigation' => [
                            'top.navbar' => [
                                'template' => 'layout/navigation'
                            ]
                        ]
                    ]
                ]
            ],
            'footer' => [
                'footer' => [
                    'template' => 'layout/footer'
                ]
            ]
        ]
    ],
    'zfcuser/login' => [
        'headTitle' => [
            'Login'
        ],
        'layout' => 'empty'
    ],
    'Application\Controller\Index' => [
        'layout' => '2cols-right',        
        'blocks' => [
            'sidebarRight' => [
                'right.dummy' => [
                    'class' => 'ConLayout\Block\Dummy',
                ]
            ],
            '_remove' => [
                'footer' => true
            ]
        ]
    ]
];
