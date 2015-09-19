<?php

namespace ConLayoutTest\View\Helper;

use ConLayout\View\Helper\BlockFactory;
use ConLayoutTest\AbstractTest;
use Zend\ServiceManager\ServiceManager;
use Zend\View\HelperPluginManager;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockFactoryTest extends AbstractTest
{
    public function testFactory()
    {
        $sm = new ServiceManager();
        $sm->setService('ConLayout\Layout\LayoutInterface', $this->layout);
        $helperManager = new HelperPluginManager();
        $helperManager->setServiceLocator($sm);

        $factory = new BlockFactory();
        $helper = $factory->createService($helperManager);

        $this->assertInstanceOf('ConLayout\View\Helper\Block', $helper);
        $this->assertInstanceOf('Zend\View\Helper\AbstractHelper', $helper);
    }
}
