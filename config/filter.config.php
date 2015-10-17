<?php
return [
    'factories' => [
        'ConLayout\Filter\BasePathFilter' => 'ConLayout\Filter\BasePathFilterFactory',
        'ConLayout\Filter\TranslateFilter' => 'ConLayout\Filter\TranslateFilterFactory',
        'ConLayout\Filter\CacheBusterFilter' => 'ConLayout\Filter\CacheBusterFilterFactory',
    ],
    'aliases' => [
        'basePath' => 'ConLayout\Filter\BasePathFilter',
        'translate' => 'ConLayout\Filter\TranslateFilter',
        'cacheBuster' => 'ConLayout\Filter\CacheBusterFilter'
    ]
];
