<?php

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayoutTest\Block;

use ConLayout\Block\Container;
use ConLayoutTest\AbstractTest;
use Zend\View\Renderer\PhpRenderer;

class ContainerTest extends AbstractTest
{
    public function testDefaultTag()
    {
        $container = new Container();
        $this->assertEquals('<div>', $container->openTag());
        $this->assertEquals('</div>', $container->closeTag());
    }

    public function testCustomTag()
    {
        $container = new Container();
        $container->setOption('tag', 'section');
        $this->assertEquals('<section>', $container->openTag());
        $this->assertEquals('</section>', $container->closeTag());
    }

    public function testAttributes()
    {
        $container = new Container();
        $container->setView(new PhpRenderer());
        $container->setOptions([
            'html_class' => 'sidebar-primary',
            'html_id' => 'sidebar'
        ]);
        $this->assertEquals(
            '<div class="sidebar-primary" id="sidebar">',
            $container->openTag()
        );
    }
}
