<?php

declare(strict_types=1);

namespace ConLayoutTest\View\Helper;

use ConLayout\View\Helper\Block;
use ConLayoutTest\AbstractTestCase;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;

class BlockTest extends AbstractTestCase
{
    /**
     *
     * @var Block
     */
    protected $blockHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->blockHelper = new Block($this->blockPool);
        $this->blockHelper->setView(new PhpRenderer());
    }

    public function testInvoke(): void
    {
        $block = new ViewModel();
        $this->blockPool->add('test.block', $block);

        $this->assertSame(
            $block,
            call_user_func_array($this->blockHelper, ['test.block']),
        );
    }
}
