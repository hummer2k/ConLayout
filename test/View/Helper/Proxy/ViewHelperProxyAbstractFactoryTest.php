<?php

declare(strict_types=1);

namespace ConLayoutTest\View\Helper\Proxy;

use ConLayout\Options\ModuleOptions;
use ConLayout\View\Helper\Proxy\ViewHelperProxyAbstractFactory;
use ConLayoutTest\AbstractTestCase;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\AbstractHelper;

class ViewHelperProxyAbstractFactoryTest extends AbstractTestCase
{
    public function testCanCreate(): void
    {
        $moduleOptions = new ModuleOptions([
            'view_helpers' => [
                'headLink' => [
                    'proxy' => TestProxy::class,
                ],
            ],
        ]);
        $sm = new ServiceManager();
        $sm->setService(ModuleOptions::class, $moduleOptions);

        $factory = new ViewHelperProxyAbstractFactory();
        $this->assertTrue($factory->canCreate($sm, TestProxy::class, TestProxy::class));
        $this->assertFalse($factory->canCreate($sm, 'not-exist', 'not-exist'));
    }
}

// @codingStandardsIgnoreStart
class TestProxy extends AbstractHelper {}
// @codingStandardsIgnoreEnd
