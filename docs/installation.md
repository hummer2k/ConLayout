# Installation

Install via composer:

`$ composer require hummer2k/conlayout:~1.1`

Enable module in your application.config.php

````php
<?php
$config = [
    'modules' => [
        'ConLayout', // <--
        'Application',
        '...'
    ]
];
````

Copy `vendor/hummer2k/conlayout/config/con-layout.global.php.dist` to
`config/autoload/con-layout.global.php`