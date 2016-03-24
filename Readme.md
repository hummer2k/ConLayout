# ConLayout 

## Master
[![Travis](https://travis-ci.org/hummer2k/ConLayout.svg?branch=master)](https://travis-ci.org/hummer2k/ConLayout)
[![Coverage Status](https://coveralls.io/repos/hummer2k/ConLayout/badge.svg?branch=master&service=github)](https://coveralls.io/github/hummer2k/ConLayout?branch=master)

## Develop
[![Travis](https://travis-ci.org/hummer2k/ConLayout.svg?branch=develop)](https://travis-ci.org/hummer2k/ConLayout)
[![Coverage Status](https://coveralls.io/repos/hummer2k/ConLayout/badge.svg?branch=develop&service=github)](https://coveralls.io/github/hummer2k/ConLayout?branch=develop)

## Installation

Install via composer:

`$ composer require hummer2k/conlayout:~3.0`

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

