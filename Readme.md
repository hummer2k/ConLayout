# ConLayout 

[![Travis](https://travis-ci.org/hummer2k/ConLayout.svg)](https://travis-ci.org/hummer2k/ConLayout)
[![Coverage Status](https://coveralls.io/repos/hummer2k/ConLayout/badge.svg?branch=master)](https://coveralls.io/r/hummer2k/ConLayout?branch=master)

## Installation

Install via composer:

`$ composer require hummerk2/conlayout:~1.1`

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

Copy `vendor/hummer2k/conlayout/config/con-layout.global.php.dist` to `config/autoload/con-layout.global.php`

