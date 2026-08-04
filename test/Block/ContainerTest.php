<?php

declare(strict_types=1);

namespace ConLayoutTest\Block;

use ConLayout\Block\Container;
use ConLayoutTest\AbstractTestCase;
use Laminas\View\Renderer\PhpRenderer;

class ContainerTest extends AbstractTestCase
{
    public function testDefaultTag(): void
    {
        $container = new Container();
        $this->assertEquals('<div>', $container->openTag());
        $this->assertEquals('</div>', $container->closeTag());
    }

    public function testCustomTag(): void
    {
        $container = new Container();
        $container->setOption('tag', 'section');
        $this->assertEquals('<section>', $container->openTag());
        $this->assertEquals('</section>', $container->closeTag());
    }

    public function testAttributes(): void
    {
        $container = new Container();
        $container->setView(new PhpRenderer());
        $container->setOptions([
            'html_class' => 'sidebar-primary',
            'html_id' => 'sidebar',
        ]);
        $this->assertEquals(
            '<div class="sidebar-primary" id="sidebar">',
            $container->openTag(),
        );
    }
}
