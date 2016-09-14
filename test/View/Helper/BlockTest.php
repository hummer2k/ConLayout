<?php

namespace ConLayoutTest\View\Helper;

use ConLayout\View\Helper\Block;
use ConLayoutTest\AbstractTest;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

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

    public function setUp()
    {
        parent::setUp();
        $this->blockHelper = new Block($this->blockPool);
        $this->blockHelper->setView(new PhpRenderer);
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
