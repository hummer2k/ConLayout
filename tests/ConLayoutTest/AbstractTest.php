<?php
namespace ConLayoutTest;

use ConLayout\Block\Factory\BlockFactory;
use ConLayoutTest\Layout\Layout;
use ConLayout\Layout\LayoutInterface;
use ConLayout\Updater\Event\UpdateEvent;
use ConLayout\Updater\LayoutUpdater;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\Config\Config;
use Zend\EventManager\EventManager;
use Zend\View\Resolver\TemplateMapResolver;
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
abstract class AbstractTest extends \PHPUnit_Framework_TestCase
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

    public function setUp()
    {
        $eventManager = new EventManager();
        $this->layoutUpdater = new LayoutUpdater();
        $this->layoutUpdater->setEventManager($eventManager);

        $this->attachGlobalLayoutStructureListener($eventManager);

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

    protected function getGlobalLayoutStructure()
    {
        return new Config([]);
    }

    protected function attachGlobalLayoutStructureListener(
        EventManager $eventManager
    )
    {
        $eventManager->getSharedManager()->clearListeners('ConLayout\Updater\LayoutUpdater');
        $eventManager->getSharedManager()->attach(
            'ConLayout\Updater\LayoutUpdater',
            'loadGlobalLayoutStructure.pre',
            function(UpdateEvent $e) {
                $globalLayoutStructure = $e->getGlobalLayoutStructure();
                $globalLayoutStructure->merge($this->getGlobalLayoutStructure());
            }
        );
    }
}
