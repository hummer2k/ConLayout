<?php
namespace ConLayoutTest;

use ConLayout\Block\BlockPool;
use ConLayout\Block\BlockPoolInterface;
use ConLayout\Block\Factory\BlockFactory;
use ConLayout\Block\Factory\BlockFactoryInterface;
use ConLayout\BlockManager;
use ConLayout\Generator\BlocksGenerator;
use ConLayout\Generator\GeneratorInterface;
use ConLayout\Layout\LayoutInterface;
use ConLayout\Module;
use ConLayout\Updater\Event\UpdateEvent;
use ConLayout\Updater\LayoutUpdater;
use ConLayout\Updater\LayoutUpdaterInterface;
use ConLayoutTest\Layout\Layout;
use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase;
use Zend\Config\Config;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Resolver\TemplateMapResolver;

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
abstract class AbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     * @var LayoutUpdaterInterface
     */
    protected $layoutUpdater;

    /**
     *
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var BlockPoolInterface
     */
    protected $blockPool;

    /**
     * @var BlockFactoryInterface
     */
    protected $blockFactory;

    /**
     * @var ContainerInterface
     */
    protected $sm;

    /**
     * @var GeneratorInterface
     */
    protected $blocksGenerator;

    /**
     *
     * @var EventManagerInterface
     */
    protected $em;

    public function setUp()
    {
        $eventManager = new EventManager();
        $this->layoutUpdater = new LayoutUpdater();
        $this->layoutUpdater->setEventManager($eventManager);
        $this->sm = Bootstrap::getServiceManager();

        $this->em = $eventManager;


        $this->blockPool = new BlockPool();
        $this->blockFactory = new BlockFactory([], new BlockManager($this->sm), $this->sm);

        $this->blocksGenerator = new BlocksGenerator(
            $this->blockFactory,
            $this->blockPool
        );

        $this->layout = new Layout(
            $this->layoutUpdater,
            $this->blockPool
        );

        $this->layout->attachGenerator(
            BlocksGenerator::NAME,
            $this->blocksGenerator
        );
    }

    protected function getResolver()
    {
        return new TemplateMapResolver([
            'widget1' => __DIR__ . '/_files/view/widget1.phtml',
            'layout' => __DIR__ . '/_files/view/layout.phtml',
            'widget-content' => __DIR__ . '/_files/view/widget-content.phtml',
            'widget-content-after' => __DIR__ . '/_files/view/widget-content-after.phtml'
        ]);
    }

    protected function getLayoutStructure()
    {
        return new Config([], true);
    }
}
