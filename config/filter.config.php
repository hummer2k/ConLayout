<?php

use ConLayout\Filter\BasePathFilter;
use ConLayout\Filter\BasePathFilterFactory;
use ConLayout\Filter\CacheBusterFilter;
use ConLayout\Filter\CacheBusterFilterFactory;
use ConLayout\Filter\ContainerFilter;
use ConLayout\Filter\TranslateFilter;
use ConLayout\Filter\TranslateFilterFactory;
use ConLayout\Filter\DebugFilter;
use ConLayout\Filter\DebugFilterFactory;

return [
    'factories' => [
        BasePathFilter::class => BasePathFilterFactory::class,
        TranslateFilter::class => TranslateFilterFactory::class,
        CacheBusterFilter::class => CacheBusterFilterFactory::class,
        DebugFilter::class => DebugFilterFactory::class,
        ContainerFilter::class => DebugFilterFactory::class
    ],
    'aliases' => [
        'basePath' => BasePathFilter::class,
        'translate' => TranslateFilter::class,
        'cacheBuster' => CacheBusterFilter::class
    ]
];
