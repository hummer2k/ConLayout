<?php

declare(strict_types=1);

namespace ConLayoutTest;

use ConLayout\Block\BlockRegistry;
use ConLayout\Block\BlockRegistryInterface;
use ConLayout\Block\Factory\BlockFactory;
use ConLayout\Block\Factory\BlockFactoryInterface;
use ConLayout\BlockManager;
use ConLayout\Generator\BlocksGenerator;
use ConLayout\Generator\GeneratorInterface;
use ConLayout\Layout\LayoutInterface;
use ConLayout\Updater\LayoutUpdater;
use ConLayout\Updater\LayoutUpdaterInterface;
use ConLayoutTest\Layout\Layout;
use Laminas\Config\Config;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerInterface;
use Laminas\View\Resolver\TemplateMapResolver;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

abstract class AbstractTestCase extends TestCase
{
    protected LayoutUpdaterInterface $layoutUpdater;

    protected LayoutInterface $layout;

    protected BlockRegistryInterface $blockRegistry;

    protected BlockFactoryInterface $blockFactory;

    protected ContainerInterface $sm;

    protected GeneratorInterface $blocksGenerator;

    protected EventManagerInterface $em;

    protected function setUp(): void
    {
        $eventManager = new EventManager();
        $this->layoutUpdater = new LayoutUpdater();
        $this->layoutUpdater->setEventManager($eventManager);
        $this->sm = Bootstrap::getServiceManager();

        $this->em = $eventManager;


        $this->blockRegistry = new BlockRegistry();
        $this->blockFactory = new BlockFactory([], new BlockManager($this->sm), $this->sm);

        $this->blocksGenerator = new BlocksGenerator(
            $this->blockFactory,
            $this->blockRegistry,
        );

        $this->layout = new Layout(
            $this->layoutUpdater,
            $this->blockRegistry,
        );

        $this->layout->attachGenerator(
            BlocksGenerator::NAME,
            $this->blocksGenerator,
        );
    }

    protected function getResolver(): TemplateMapResolver
    {
        return new TemplateMapResolver([
            'widget1' => __DIR__ . '/_files/view/widget1.phtml',
            'layout' => __DIR__ . '/_files/view/layout.phtml',
            'widget-content' => __DIR__ . '/_files/view/widget-content.phtml',
            'widget-content-after' => __DIR__ . '/_files/view/widget-content-after.phtml',
        ]);
    }

    protected function getLayoutStructure(): Config
    {
        return new Config([], true);
    }
}
