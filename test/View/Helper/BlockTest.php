<?php

namespace ConLayoutTest\View\Helper;

use ConLayout\View\Helper\Block;
use ConLayoutTest\AbstractTest;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockTest extends AbstractTest
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

    public function testInvoke()
    {
        $block = new ViewModel();
        $this->blockPool->add('test.block', $block);

        $this->assertSame(
            $block,
            call_user_func_array($this->blockHelper, ['test.block'])
        );
    }
}
