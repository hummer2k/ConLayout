<?php

namespace ConLayoutTest\Handle;

use ConLayout\Handle\Handle;
use ConLayoutTest\AbstractTest;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class HandleTest extends AbstractTest
{
    public function testHandle()
    {
        $handle = new Handle('my-handle', 10);
        $this->assertEquals('my-handle', $handle->getName());
        $this->assertEquals(10, $handle->getPriority());
        $this->assertEquals('my-handle', (string) $handle);
    }

}