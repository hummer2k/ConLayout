<?php

declare(strict_types=1);

namespace ConLayoutTest\View\Helper\Proxy;

use ConLayout\View\Helper\Proxy\HeadLinkProxy;
use PHPUnit\Framework\TestCase;
use Laminas\View\Helper\AbstractHelper;

class AbstractProxyTest extends TestCase
{
    public function testDelegatesNotExistingMethodsToSubject(): void
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

    public function call(): void
    {
        $this->called = true;
    }

    public function isCalled(): bool
    {
        return $this->called;
    }
}
// @codingStandardsIgnoreEnd
