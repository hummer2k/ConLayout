<?php

use ConLayout\Filter\BasePathFilter;
use ConLayout\Filter\BasePathFilterFactory;
use ConLayout\Filter\CacheBusterFilter;
use ConLayout\Filter\CacheBusterFilterFactory;
use ConLayout\Filter\TranslateFilter;
use ConLayout\Filter\TranslateFilterFactory;

return [
    'factories' => [
        BasePathFilter::class => BasePathFilterFactory::class,
        TranslateFilter::class => TranslateFilterFactory::class,
        CacheBusterFilter::class => CacheBusterFilterFactory::class,
    ],
    'aliases' => [
        'basePath' => BasePathFilter::class,
        'translate' => TranslateFilter::class,
        'cacheBuster' => CacheBusterFilter::class
    ]
];
