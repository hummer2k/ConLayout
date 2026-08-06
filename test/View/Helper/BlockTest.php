<?php

declare(strict_types=1);

namespace ConLayoutTest\View\Helper;

use ConLayout\View\Helper\Block;
use ConLayoutTest\AbstractTestCase;
use Laminas\View\Model\ViewModel;

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
    }

    public function testInvoke(): void
    {
        $block = new ViewModel();
        $this->blockPool->add('test.block', $block);

        $this->assertSame(
            $block,
            ($this->blockHelper)('test.block'),
        );
    }
}
