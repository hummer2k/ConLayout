<?php

namespace ConLayoutTest;

use ConLayout\Block\AbstractBlock;
use ConLayout\BlockManager;
use Zend\View\Model\ViewModel;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockManagerTest extends AbstractTest
{
    /**
     *
     * @var BlockManager
     */
    protected $blockManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->blockManager = new BlockManager($this->sm);
    }

    public function testValidate()
    {
        $viewModel = new ViewModel();
        $this->assertNull($this->blockManager->validatePlugin($viewModel));

        $this->assertNull($this->blockManager->validatePlugin(new TestBlock()));
    }

    public function testThrowsExceptionForInvalidBlock()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->blockManager->validatePlugin(new \stdClass());
    }
}
// @codingStandardsIgnoreStart
class TestBlock extends AbstractBlock
{
}
// @codingStandardsIgnoreEnd
