<?php

use ConLayout\Block\BlockPool;
use ConLayout\Block\BlockPoolInterface;
use ConLayout\Block\Factory\BlockFactoryFactory;
use ConLayout\Block\Factory\BlockFactoryInterface;
use ConLayout\BlockManager;
use ConLayout\BlockManagerFactory;
use ConLayout\Layout\LayoutFactory;
use ConLayout\Layout\LayoutInterface;
use ConLayout\Listener\ActionHandlesListener;
use ConLayout\Listener\BodyClassListener;
use ConLayout\Listener\Factory\ActionHandlesListenerFactory;
use ConLayout\Listener\Factory\BodyClassListenerFactory;
use ConLayout\Listener\Factory\LayoutTemplateListenerFactory;
use ConLayout\Listener\Factory\LayoutUpdateListenerFactory;
use ConLayout\Listener\Factory\LoadLayoutListenerFactory;
use ConLayout\Listener\Factory\ViewHelperListenerFactory;
use ConLayout\Listener\LayoutTemplateListener;
use ConLayout\Listener\LayoutUpdateListener;
use ConLayout\Listener\LoadLayoutListener;
use ConLayout\Listener\PrepareActionViewModelListener;
use ConLayout\Listener\ViewHelperListener;
use ConLayout\Options\ModuleOptions;
use ConLayout\Options\ModuleOptionsFactory;
use ConLayout\Updater\LayoutUpdaterFactory;
use ConLayout\Updater\LayoutUpdaterInterface;
use ConLayout\View\Renderer\BlockRenderer;
use ConLayout\View\Renderer\BlockRendererFactory;
use ConLayout\Zdt\Collector\LayoutCollector;
use ConLayout\Zdt\Collector\LayoutCollectorFactory;
use ConLayout\Generator\BlocksGenerator;
use ConLayout\Generator\BlocksGeneratorFactory;
use ConLayout\Generator\ViewHelperGenerator;
use ConLayout\Generator\ViewHelperGeneratorFactory;

return [
    'factories' => [
        BlockManager::class             => BlockManagerFactory::class,
        BlockFactoryInterface::class    => BlockFactoryFactory::class,
        ActionHandlesListener::class    => ActionHandlesListenerFactory::class,
        BodyClassListener::class        => BodyClassListenerFactory::class,
        LoadLayoutListener::class       => LoadLayoutListenerFactory::class,
        LayoutTemplateListener::class   => LayoutTemplateListenerFactory::class,
        LayoutUpdateListener::class     => LayoutUpdateListenerFactory::class,
        LayoutInterface::class          => LayoutFactory::class,
        LayoutUpdaterInterface::class   => LayoutUpdaterFactory::class,
        BlockRenderer::class            => BlockRendererFactory::class,
        LayoutCollector::class          => LayoutCollectorFactory::class,
        ModuleOptions::class            => ModuleOptionsFactory::class,
        BlocksGenerator::class          => BlocksGeneratorFactory::class,
        ViewHelperGenerator::class      => ViewHelperGeneratorFactory::class
    ],
    'aliases' => [
        'Layout'                => LayoutInterface::class,
        'BlockRenderer'         => BlockRenderer::class,
        'BlockManager'          => BlockManager::class
    ],
    'invokables' => [
        BlockPoolInterface::class => BlockPool::class,
        PrepareActionViewModelListener::class => PrepareActionViewModelListener::class
    ]
];
