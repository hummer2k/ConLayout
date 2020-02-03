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
use ConLayout\Listener\ActionHandlesListener;
use ConLayout\Listener\BodyClassListener;
use ConLayout\Listener\Factory\ActionHandlesListenerFactory;
use ConLayout\Listener\Factory\BodyClassListenerFactory;
use ConLayout\Listener\Factory\LoadLayoutListenerFactory;
use ConLayout\Listener\LoadLayoutListener;
use ConLayout\Listener\PrepareActionViewModelListener;
use ConLayout\Options\ModuleOptions;
use ConLayout\Options\ModuleOptionsFactory;
use ConLayout\Updater\LayoutUpdaterFactory;
use ConLayout\Updater\LayoutUpdaterInterface;
use ConLayout\Ldt\Collector\LayoutCollector;
use ConLayout\Ldt\Collector\LayoutCollectorFactory;
use ConLayout\Listener\Factory\PrepareActionViewModelListenerFactory;
use ConLayout\Updater\Collector\FilesystemCollector;
use ConLayout\Updater\Collector\FilesystemCollectorFactory;
use ConLayout\Updater\Collector\ConfigCollector;
use ConLayout\Updater\Collector\ConfigCollectorFactory;

return [
    'factories' => [
        FilesystemCollector::class      => FilesystemCollectorFactory::class,
        ConfigCollector::class          => ConfigCollectorFactory::class,
        BlockManager::class             => BlockManagerFactory::class,
        BlockFactoryInterface::class    => BlockFactoryFactory::class,
        ActionHandlesListener::class    => ActionHandlesListenerFactory::class,
        BodyClassListener::class        => BodyClassListenerFactory::class,
        LoadLayoutListener::class       => LoadLayoutListenerFactory::class,
        PrepareActionViewModelListener::class => PrepareActionViewModelListenerFactory::class,
        LayoutInterface::class          => LayoutFactory::class,
        LayoutUpdaterInterface::class   => LayoutUpdaterFactory::class,
        LayoutCollector::class          => LayoutCollectorFactory::class,
        ModuleOptions::class            => ModuleOptionsFactory::class,
        BlocksGenerator::class          => BlocksGeneratorFactory::class,
        ViewHelperGenerator::class      => ViewHelperGeneratorFactory::class
    ],
    'aliases' => [
        'Layout'                => LayoutInterface::class,
        'BlockManager'          => BlockManager::class
    ],
    'invokables' => [
        BlockPoolInterface::class => BlockPool::class,
    ]
];
