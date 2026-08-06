<?php

declare(strict_types=1);

namespace ConLayoutTest\View\Helper;

use ConLayout\View\Helper\Wrapper;
use ConLayoutTest\AbstractTestCase;
use Laminas\Escaper\Escaper;
use Laminas\View\Helper\HtmlAttributes;

class WrapperTest extends AbstractTestCase
{
    /**
     *
     * @var Wrapper
     */
    private $wrapperHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wrapperHelper = new Wrapper(new HtmlAttributes(new Escaper()));
    }

    public function testDefaultOpeningTag(): void
    {
        ($this->wrapperHelper)();
        $this->assertEquals(
            '<' . Wrapper::DEFAULT_TAG . '>',
            $this->wrapperHelper->openTag(),
        );
    }

    public function testOpenTagHasAttributes(): void
    {
        $tag = 'aside';
        ($this->wrapperHelper)($tag);
        $this->assertEquals(
            '<' . $tag . ' class="col-xs-12" id="my-wrapper">',
            $this->wrapperHelper->openTag(['class' => 'col-xs-12', 'id' => 'my-wrapper']),
        );
    }

    public function testClosingTag(): void
    {
        ($this->wrapperHelper)();
        $this->assertEquals('</div>', $this->wrapperHelper->closeTag());
    }
}
