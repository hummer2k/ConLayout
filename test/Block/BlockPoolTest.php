<?php

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayoutTest\Block;

use ConLayoutTest\AbstractTest;
use Laminas\View\Model\ViewModel;

class BlockPoolTest extends AbstractTest
{
    public function testAddAndGetBlock()
    {
        $block = new ViewModel();
        $this->blockPool->add('my-block', $block);
        $this->assertSame($block, $this->blockPool->get('my-block'));
    }

    public function testAddSortBlockWithBeforeAndAfter()
    {
        $block1 = new ViewModel([], ['after' => 'block2']);
        $block2 = new Viewmodel();
        $block3 = new ViewModel([], ['before' => 'block2']);
        $block4 = new ViewModel([], ['before' => 'block3']);
        $block5 = new ViewModel([], ['after' => 'block1']);

        $expectedOrder = [
            'block4',
            'block3',
            'block2',
            'block1',
            'block5'
        ];

        $this->blockPool->add('block1', $block1);
        $this->blockPool->add('block2', $block2);
        $this->blockPool->add('block3', $block3);
        $this->blockPool->add('block4', $block4);
        $this->blockPool->add('block5', $block5);

        $this->blockPool->sort();

        $this->assertSame($expectedOrder, array_keys($this->blockPool->get()));
    }

    public function testAddBlockWithChildren()
    {
        $block1 = new ViewModel();
        $block2 = new ViewModel([], [
            'block_id' => 'child1'
        ]);
        $block3 = new ViewModel([], [
            'block_id' => 'child2'
        ]);
        $block1->addChild($block2);
        $block1->addChild($block3);

        $this->blockPool->add('my-block', $block1);

        $this->assertCount(3, $this->blockPool->get());

        $this->assertSame(
            $block2,
            $this->blockPool->get('child1')
        );

        $this->assertSame(
            $block3,
            $this->blockPool->get('child2')
        );
    }

    public function testAddBlockWithChildChildren()
    {
        $block1 = new ViewModel();
        $block2 = new ViewModel([], [
            'block_id' => 'child1'
        ]);
        $block3 = new ViewModel([], [
            'block_id' => 'child2'
        ]);
        $block1->addChild($block2);
        $block2->addChild($block3);

        $this->blockPool->add('my-block', $block1);

        $this->assertCount(3, $this->blockPool->get());

        $this->assertSame(
            $block2,
            $this->blockPool->get('child1')
        );

        $this->assertSame(
            $block3,
            $this->blockPool->get('child2')
        );
    }

    public function testGetBlocks()
    {
        $blocks = $this->blockPool->get();

        $this->assertIsArray($blocks);
    }

    public function testSortBlocks()
    {
        
        $expectedOrder = [
            1 => 'block-2',
            2 => 'block-4',
            3 => 'block-1',
            4 => 'block-3'
        ];

        $block1 = new ViewModel([], ['order' => 5]);
        $block2 = new ViewModel([], ['order' => -10]);
        $block3 = new ViewModel([], ['order' => 10]);
        $block4 = new ViewModel();

        $this->blockPool->add('block-1', $block1);
        $this->blockPool->add('block-2', $block2);
        $this->blockPool->add('block-3', $block3);
        $this->blockPool->add('block-4', $block4);

        $this->blockPool->sort();

        $i = 1;
        foreach (array_keys($this->blockPool->get()) as $blockId) {
            $this->assertEquals($expectedOrder[$i], $blockId);
            $i++;
        }
    }

    public function testRemoveAddedBlock()
    {
        $block = new ViewModel();
        $this->blockPool->add('some-block', $block);
        $this->assertSame($block, $this->blockPool->get('some-block'));

        $this->blockPool->remove('some-block');

        $this->assertFalse($this->blockPool->get('some-block'));
    }

    public function testAnonymousBlockId()
    {
        $viewModel = new ViewModel();
        $child = new ViewModel();
        $viewModel->addChild($child);
        $this->blockPool->add('test.block', $viewModel);

        $this->assertEquals(
            'anonymous.content.1',
            $child->getOption('block_id')
        );
    }
}
