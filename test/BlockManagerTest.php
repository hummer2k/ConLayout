<?php

declare(strict_types=1);

namespace ConLayoutTest;

use ConLayout\Block\AbstractBlock;
use ConLayout\BlockManager;
use Laminas\View\Model\ViewModel;

class BlockManagerTest extends AbstractTestCase
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

    public function testValidate(): void
    {
        $viewModel = new ViewModel();
        $this->assertNull($this->blockManager->validatePlugin($viewModel));

        $this->assertNull($this->blockManager->validatePlugin(new TestBlock()));
    }

    public function testThrowsExceptionForInvalidBlock(): void
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
