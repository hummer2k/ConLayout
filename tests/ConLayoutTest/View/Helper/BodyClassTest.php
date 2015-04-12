<?php
namespace ConLayoutTest\View\Helper;

use ConLayout\View\Helper\BodyClass;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BodyClassTest extends \PHPUnit_Framework_TestCase
{
    public function testBodyClassHelper()
    {
        $helper = new BodyClass();
        $helper('my-class');
        $helper->addClass('other-class');

        $this->assertSame('my-class other-class', (string) $helper);
    }
}