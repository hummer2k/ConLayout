<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/test');

return (new Config())
    ->setRules([
        '@PER-CS' => true,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(false);
