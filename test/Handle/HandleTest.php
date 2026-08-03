<?php

declare(strict_types=1);

namespace ConLayoutTest\Handle;

use ConLayout\Handle\Handle;
use ConLayoutTest\AbstractTestCase;

class HandleTest extends AbstractTestCase
{
    public function testHandle(): void
    {
        $handle = new Handle('my-handle', 10);
        $this->assertEquals('my-handle', $handle->getName());
        $this->assertEquals(10, $handle->getPriority());
        $this->assertEquals('my-handle', (string) $handle);
    }
}
