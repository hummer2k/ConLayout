<?php

declare(strict_types=1);

namespace ConLayoutTest\Block;

use ConLayoutTest\AbstractTestCase;
use Laminas\View\Model\ViewModel;

class BlockRegistryTest extends AbstractTestCase
{
    public function testAddAndGetBlock(): void
    {
        $block = new ViewModel();
        $this->blockPool->add('my-block', $block);
        $this->assertSame($block, $this->blockPool->get('my-block'));
    }

    public function testAddSortBlockWithBeforeAndAfter(): void
    {
        $block1 = $this->createBlock(['after' => 'block2']);
        $block2 = new ViewModel();
        $block3 = $this->createBlock(['before' => 'block2']);
        $block4 = $this->createBlock(['before' => 'block3']);
        $block5 = $this->createBlock(['after' => 'block1']);

        $expectedOrder = [
            'block4',
            'block3',
            'block2',
            'block1',
            'block5',
        ];

        $this->blockPool->add('block1', $block1);
        $this->blockPool->add('block2', $block2);
        $this->blockPool->add('block3', $block3);
        $this->blockPool->add('block4', $block4);
        $this->blockPool->add('block5', $block5);

        $this->blockPool->sort();

        $this->assertSame($expectedOrder, array_keys($this->blockPool->get()));
    }

    public function testAddBlockWithChildren(): void
    {
        $block1 = new ViewModel();
        $block2 = $this->createBlock(['block_id' => 'child1']);
        $block3 = $this->createBlock(['block_id' => 'child2']);
        $block1->addChild($block2);
        $block1->addChild($block3);

        $this->blockPool->add('my-block', $block1);

        $this->assertCount(3, $this->blockPool->get());

        $this->assertSame(
            $block2,
            $this->blockPool->get('child1'),
        );

        $this->assertSame(
            $block3,
            $this->blockPool->get('child2'),
        );
    }

    public function testAddBlockWithChildChildren(): void
    {
        $block1 = new ViewModel();
        $block2 = $this->createBlock(['block_id' => 'child1']);
        $block3 = $this->createBlock(['block_id' => 'child2']);
        $block1->addChild($block2);
        $block2->addChild($block3);

        $this->blockPool->add('my-block', $block1);

        $this->assertCount(3, $this->blockPool->get());

        $this->assertSame(
            $block2,
            $this->blockPool->get('child1'),
        );

        $this->assertSame(
            $block3,
            $this->blockPool->get('child2'),
        );
    }

    public function testGetBlocks(): void
    {
        $blocks = $this->blockPool->get();

        $this->assertIsArray($blocks);
    }

    public function testSortBlocks(): void
    {

        $expectedOrder = [
            1 => 'block-2',
            2 => 'block-4',
            3 => 'block-1',
            4 => 'block-3',
        ];

        $block1 = $this->createBlock(['order' => 5]);
        $block2 = $this->createBlock(['order' => -10]);
        $block3 = $this->createBlock(['order' => 10]);
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

    public function testSortBlocksOnlyWithinTheirParents(): void
    {
        $block1 = $this->createBlock(['order' => 2]);
        $block2 = $this->createBlock(['order' => 1]);
        $block3 = $this->createBlock(['order' => 10]);
        $block3->setCaptureTo('my.parent::child');
        $block4 = $this->createBlock(['order' => -10]);
        $block4->setCaptureTo('my.parent::child');

        $this->blockPool->add('block1', $block1);
        $this->blockPool->add('block2', $block2);
        $this->blockPool->add('my.parent.child1', $block3);
        $this->blockPool->add('my.parent.child2', $block4);

        $this->blockPool->sort();

        $this->assertSame(
            ['block2', 'block1', 'my.parent.child2', 'my.parent.child1'],
            array_keys($this->blockPool->get()),
        );
    }

    public function testBeforeAndAfterAreIgnoredAcrossParents(): void
    {
        $block1 = $this->createBlock(['order' => 10]);
        $block2 = $this->createBlock(['before' => 'block1']);
        $block2->setCaptureTo('my.parent::child');
        $block3 = $this->createBlock(['order' => 5]);
        $block3->setCaptureTo('my.parent::child');

        $this->blockPool->add('block1', $block1);
        $this->blockPool->add('my.parent.child1', $block2);
        $this->blockPool->add('my.parent.child2', $block3);

        $this->blockPool->sort();

        $this->assertSame(
            ['block1', 'my.parent.child1', 'my.parent.child2'],
            array_keys($this->blockPool->get()),
        );
    }

    public function testBeforeAndAfterWithinSameParent(): void
    {
        $block1 = $this->createBlock(['after' => 'my.parent.child2']);
        $block1->setCaptureTo('my.parent::child');
        $block2 = new ViewModel();
        $block2->setCaptureTo('my.parent::child');
        $block3 = $this->createBlock(['before' => 'my.parent.child2']);
        $block3->setCaptureTo('my.parent::child');

        $this->blockPool->add('my.parent.child1', $block1);
        $this->blockPool->add('my.parent.child2', $block2);
        $this->blockPool->add('my.parent.child3', $block3);

        $this->blockPool->sort();

        $this->assertSame(
            ['my.parent.child3', 'my.parent.child2', 'my.parent.child1'],
            array_keys($this->blockPool->get()),
        );
    }

    public function testRemoveAddedBlock(): void
    {
        $block = new ViewModel();
        $this->blockPool->add('some-block', $block);
        $this->assertSame($block, $this->blockPool->get('some-block'));

        $this->blockPool->remove('some-block');

        $this->assertFalse($this->blockPool->get('some-block'));
    }

    public function testGetRemovedBlocks(): void
    {
        $block = new ViewModel();
        $this->blockPool->add('some-block', $block);

        $this->assertSame([], $this->blockPool->getRemovedBlocks());

        $this->blockPool->remove('some-block');
        $this->blockPool->remove('never-added');

        $this->assertSame(['some-block', 'never-added'], $this->blockPool->getRemovedBlocks());
    }

    public function testAnonymousBlockId(): void
    {
        $viewModel = new ViewModel();
        $child = new ViewModel();
        $viewModel->addChild($child);
        $this->blockPool->add('test.block', $viewModel);

        $this->assertEquals(
            'anonymous.content.1',
            $child->getVariable('block_id'),
        );
    }

    private function createBlock(array $metadata): ViewModel
    {
        $block = new ViewModel();
        foreach ($metadata as $key => $value) {
            $block->setVariable($key, $value);
        }
        return $block;
    }
}
