<?php

declare(strict_types=1);

namespace ConLayoutTest\View\Helper;

use ConLayout\View\Helper\BodyClass;

class BodyClassTest extends \PHPUnit\Framework\TestCase
{
    public function testBodyClassHelper(): void
    {
        $helper = new BodyClass();
        $helper('my-class');
        $helper->addClass('other-class');

        $this->assertSame('my-class other-class', (string) $helper);

        $helper->removeClass('other-class');

        $this->assertSame('my-class', (string) $helper);
    }
}
