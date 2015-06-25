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

    public function setUp()
    {
        parent::setUp();
        $this->blockManager = new BlockManager();
    }

    public function testValidate()
    {
        $viewModel = new ViewModel();
        $this->assertNull($this->blockManager->validatePlugin($viewModel));

        $this->assertNull($this->blockManager->validatePlugin(new TestBlock()));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testThrowsExceptionForInvalidBlock()
    {
        $this->blockManager->validatePlugin(new \stdClass());
    }
}

class TestBlock extends AbstractBlock
{
}
