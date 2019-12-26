<?php
namespace ConLayoutTest\View\Helper;

use ConLayout\View\Helper\BodyClass;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BodyClassTest extends \PHPUnit\Framework\TestCase
{
    public function testBodyClassHelper()
    {
        $helper = new BodyClass();
        $helper('my-class');
        $helper->addClass('other-class');

        $this->assertSame('my-class other-class', (string) $helper);

        $helper->removeClass('other-class');

        $this->assertSame('my-class', (string) $helper);
    }
}
