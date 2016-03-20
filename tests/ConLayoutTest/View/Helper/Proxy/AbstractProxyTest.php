<?php

namespace ConLayoutTest\View\Helper\Proxy;

use ConLayout\View\Helper\Proxy\HeadLinkProxy;
use PHPUnit_Framework_TestCase;
use Zend\View\Helper\AbstractHelper;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class AbstractProxyTest extends PHPUnit_Framework_TestCase
{
    public function testDelegatesNotExistingMethodsToSubject()
    {
        $helper = new TestHelper();
        $proxy = new HeadLinkProxy($helper);
        $proxy->call();
        $this->assertTrue($helper->isCalled());
    }
}

// @codingStandardsIgnoreStart
class TestHelper extends AbstractHelper
{
    protected $called = false;

    public function call()
    {
        $this->called = true;
    }

    public function isCalled()
    {
        return $this->called;
    }
}
// @codingStandardsIgnoreEnd