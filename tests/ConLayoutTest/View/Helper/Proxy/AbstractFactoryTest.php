<?php

namespace ConLayoutTest\View\Helper\Proxy;

use ConLayout\Options\ModuleOptions;
use ConLayout\View\Helper\Proxy\ViewHelperProxyAbstractFactory;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Helper\AbstractHelper;
use Zend\View\HelperPluginManager;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ViewHelperProxyAbstractFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCanCreateServiceWithName()
    {
        $moduleOptions = new ModuleOptions([
            'view_helpers' => [
                'headLink' => [
                    'proxy' => TestProxy::class
                ]
            ]
        ]);
        $sm = new ServiceManager();
        $sm->setService(ModuleOptions::class, $moduleOptions);
        $viewHelperManager = new HelperPluginManager();
        $viewHelperManager->setServiceLocator($sm);
        $viewHelperManager->setInvokableClass(TestProxy::class, TestProxy::class);

        $factory = new ViewHelperProxyAbstractFactory();
        $this->assertTrue($factory->canCreateServiceWithName($viewHelperManager, TestProxy::class, TestProxy::class));
        $this->assertFalse($factory->canCreateServiceWithName($viewHelperManager, 'not-exist', 'not-exist'));
    }
}

// @codingStandardsIgnoreStart
class TestProxy extends AbstractHelper
{

}
// @codingStandardsIgnoreEnd