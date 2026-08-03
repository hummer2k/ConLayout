<?php

use ConLayout\Block\BlockPool;
use ConLayout\Block\BlockPoolInterface;
use ConLayout\Block\Factory\BlockFactoryFactory;
use ConLayout\Block\Factory\BlockFactoryInterface;
use ConLayout\BlockManager;
use ConLayout\BlockManagerFactory;
use ConLayout\Generator\BlocksGenerator;
use ConLayout\Generator\BlocksGeneratorFactory;
use ConLayout\Generator\ViewHelperGenerator;
use ConLayout\Generator\ViewHelperGeneratorFactory;
use ConLayout\Layout\LayoutFactory;
use ConLayout\Layout\LayoutInterface;
use ConLayout\LayoutManager;
use ConLayout\LayoutManagerFactory;
use ConLayout\Middleware\BodyClassMiddleware;
use ConLayout\Middleware\BodyClassMiddlewareFactory;
use ConLayout\Middleware\HandleExtractionMiddleware;
use ConLayout\Middleware\HandleExtractionMiddlewareFactory;
use ConLayout\Middleware\LayoutContextMiddleware;
use ConLayout\Middleware\LayoutContextMiddlewareFactory;
use ConLayout\Middleware\LayoutMiddleware;
use ConLayout\Middleware\LayoutMiddlewareFactory;
use ConLayout\Middleware\PrepareActionResultMiddleware;
use ConLayout\Middleware\PrepareActionResultMiddlewareFactory;
use ConLayout\Options\ModuleOptions;
use ConLayout\Options\ModuleOptionsFactory;
use ConLayout\Updater\Collector\ConfigCollector;
use ConLayout\Updater\Collector\ConfigCollectorFactory;
use ConLayout\Updater\Collector\FilesystemCollector;
use ConLayout\Updater\Collector\FilesystemCollectorFactory;
use ConLayout\Updater\LayoutUpdaterFactory;
use ConLayout\Updater\LayoutUpdaterInterface;
use ConLayout\View\PhpRendererDelegatorFactory;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;

return [
    'factories' => [
        FilesystemCollector::class             => FilesystemCollectorFactory::class,
        ConfigCollector::class                 => ConfigCollectorFactory::class,
        BlockManager::class                    => BlockManagerFactory::class,
        BlockFactoryInterface::class           => BlockFactoryFactory::class,
        LayoutInterface::class                 => LayoutFactory::class,
        LayoutUpdaterInterface::class          => LayoutUpdaterFactory::class,
        ModuleOptions::class                   => ModuleOptionsFactory::class,
        BlocksGenerator::class                 => BlocksGeneratorFactory::class,
        ViewHelperGenerator::class             => ViewHelperGeneratorFactory::class,
        LayoutManager::class                   => LayoutManagerFactory::class,
        LayoutMiddleware::class                => LayoutMiddlewareFactory::class,
        LayoutContextMiddleware::class         => LayoutContextMiddlewareFactory::class,
        HandleExtractionMiddleware::class      => HandleExtractionMiddlewareFactory::class,
        BodyClassMiddleware::class             => BodyClassMiddlewareFactory::class,
        PrepareActionResultMiddleware::class   => PrepareActionResultMiddlewareFactory::class
    ],
    'aliases' => [
        'Layout'                => LayoutInterface::class,
        'BlockManager'          => BlockManager::class,
        'ViewRenderer'          => PhpRenderer::class,
        'ViewHelperManager'     => HelperPluginManager::class
    ],
    'invokables' => [
        BlockPoolInterface::class => BlockPool::class,
        PhpRenderer::class        => PhpRenderer::class
    ],
    'delegators' => [
        PhpRenderer::class => [
            PhpRendererDelegatorFactory::class
        ]
    ]
];
