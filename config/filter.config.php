<?php

use ConLayout\Filter\BasePathFilter;
use ConLayout\Filter\BasePathFilterFactory;
use ConLayout\Filter\CacheBusterFilter;
use ConLayout\Filter\CacheBusterFilterFactory;
use ConLayout\Filter\ContainerFilter;
use ConLayout\Filter\DebugFilter;
use ConLayout\Filter\DebugFilterFactory;

return [
    'factories' => [
        BasePathFilter::class => BasePathFilterFactory::class,
        CacheBusterFilter::class => CacheBusterFilterFactory::class,
        DebugFilter::class => DebugFilterFactory::class,
        ContainerFilter::class => DebugFilterFactory::class
    ],
    'aliases' => [
        'basePath' => BasePathFilter::class,
        'cacheBuster' => CacheBusterFilter::class
    ]
];
