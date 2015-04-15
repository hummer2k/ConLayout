<?php

return [
    'default' => [        
        'layout' => 'layout/2cols-left',
        'headLink' => [
            'assets/css/bootstrap.css',
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
                    'template' => 'layout/partials/head'
                ]
            ],
            'header' => [
                'header' => [
                    'template' => 'layout/partials/header',
                    'children' => [
                        'navigation' => [
                            'top.navbar' => [
                                'template' => 'layout/partials/navigation'
                            ]
                        ]
                    ]
                ]
            ],
            'footer' => [
                'footer' => [
                    'template' => 'layout/partials/footer'
                ]
            ]
        ]
    ],
    'zfcuser/login' => [
        'headTitle' => [
            'Login'
        ],
        'layout' => 'layout/empty'
    ],
    'Application\Controller\Index' => [
        'layout' => 'layout/2cols-right',
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
