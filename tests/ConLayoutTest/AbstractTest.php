<?php
namespace ConLayoutTest;

use ConLayout\Block\Factory\BlockFactory;
use ConLayout\BlockManager;
use ConLayout\Layout\LayoutInterface;
use ConLayout\Updater\Event\UpdateEvent;
use ConLayout\Updater\LayoutUpdater;
use ConLayout\Updater\LayoutUpdaterInterface;
use ConLayoutTest\Layout\Layout;
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
     *
     * @var EventManagerInterface
     */
    protected $em;

    public function setUp()
    {
        $eventManager = new EventManager();
        $this->layoutUpdater = new LayoutUpdater();
        $this->layoutUpdater->setEventManager($eventManager);

        $this->em = $eventManager;
        $this->em->getSharedManager()->clearListeners('ConLayout\Updater\LayoutUpdater');
        $this->em->getSharedManager()->attach(
            'ConLayout\Updater\LayoutUpdater',
            'getLayoutStructure.pre',
            function (UpdateEvent $e) {
                $layoutStructure = $e->getLayoutStructure();
                $layoutStructure->merge($this->getLayoutStructure());
            }
        );

        $this->layout = new Layout(
            new BlockFactory(),
            $this->layoutUpdater
        );
    }

    protected function getResolver()
    {
        return new TemplateMapResolver([
            'widget1' => __DIR__ . '/view/widget1.phtml',
            'layout' => __DIR__ . '/view/layout.phtml',
            'widget-content' => __DIR__ . '/view/widget-content.phtml',
            'widget-content-after' => __DIR__ . '/view/widget-content-after.phtml'
        ]);
    }

    protected function getLayoutStructure()
    {
        return new Config([], true);
    }
}
