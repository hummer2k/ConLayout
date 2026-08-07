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
        $this->blockRegistry->add('my-block', $block);
        $this->assertSame($block, $this->blockRegistry->get('my-block'));
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

        $this->blockRegistry->add('block1', $block1);
        $this->blockRegistry->add('block2', $block2);
        $this->blockRegistry->add('block3', $block3);
        $this->blockRegistry->add('block4', $block4);
        $this->blockRegistry->add('block5', $block5);

        $this->blockRegistry->sort();

        $this->assertSame($expectedOrder, array_keys($this->blockRegistry->getAll()));
    }

    public function testAddBlockWithChildren(): void
    {
        $block1 = new ViewModel();
        $block2 = $this->createBlock(['blockId' => 'child1']);
        $block3 = $this->createBlock(['blockId' => 'child2']);
        $block1->addChild($block2);
        $block1->addChild($block3);

        $this->blockRegistry->add('my-block', $block1);

        $this->assertCount(3, $this->blockRegistry->getAll());

        $this->assertSame(
            $block2,
            $this->blockRegistry->get('child1'),
        );

        $this->assertSame(
            $block3,
            $this->blockRegistry->get('child2'),
        );
    }

    public function testAddBlockWithChildChildren(): void
    {
        $block1 = new ViewModel();
        $block2 = $this->createBlock(['blockId' => 'child1']);
        $block3 = $this->createBlock(['blockId' => 'child2']);
        $block1->addChild($block2);
        $block2->addChild($block3);

        $this->blockRegistry->add('my-block', $block1);

        $this->assertCount(3, $this->blockRegistry->getAll());

        $this->assertSame(
            $block2,
            $this->blockRegistry->get('child1'),
        );

        $this->assertSame(
            $block3,
            $this->blockRegistry->get('child2'),
        );
    }

    public function testGetBlocks(): void
    {
        $blocks = $this->blockRegistry->getAll();

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

        $this->blockRegistry->add('block-1', $block1);
        $this->blockRegistry->add('block-2', $block2);
        $this->blockRegistry->add('block-3', $block3);
        $this->blockRegistry->add('block-4', $block4);

        $this->blockRegistry->sort();

        $i = 1;
        foreach (array_keys($this->blockRegistry->getAll()) as $blockId) {
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

        $this->blockRegistry->add('block1', $block1);
        $this->blockRegistry->add('block2', $block2);
        $this->blockRegistry->add('my.parent.child1', $block3);
        $this->blockRegistry->add('my.parent.child2', $block4);

        $this->blockRegistry->sort();

        $this->assertSame(
            ['block2', 'block1', 'my.parent.child2', 'my.parent.child1'],
            array_keys($this->blockRegistry->getAll()),
        );
    }

    public function testBeforeAndAfterAreIgnoredAcrossParents(): void
    {
        $block1 = $this->createBlock(['order' => 10]);
        $block2 = $this->createBlock(['before' => 'block1']);
        $block2->setCaptureTo('my.parent::child');
        $block3 = $this->createBlock(['order' => 5]);
        $block3->setCaptureTo('my.parent::child');

        $this->blockRegistry->add('block1', $block1);
        $this->blockRegistry->add('my.parent.child1', $block2);
        $this->blockRegistry->add('my.parent.child2', $block3);

        $this->blockRegistry->sort();

        $this->assertSame(
            ['block1', 'my.parent.child1', 'my.parent.child2'],
            array_keys($this->blockRegistry->getAll()),
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

        $this->blockRegistry->add('my.parent.child1', $block1);
        $this->blockRegistry->add('my.parent.child2', $block2);
        $this->blockRegistry->add('my.parent.child3', $block3);

        $this->blockRegistry->sort();

        $this->assertSame(
            ['my.parent.child3', 'my.parent.child2', 'my.parent.child1'],
            array_keys($this->blockRegistry->getAll()),
        );
    }

    public function testRemoveAddedBlock(): void
    {
        $block = new ViewModel();
        $this->blockRegistry->add('some-block', $block);
        $this->assertSame($block, $this->blockRegistry->get('some-block'));

        $this->blockRegistry->remove('some-block');

        $this->assertFalse($this->blockRegistry->get('some-block'));
    }

    public function testGetRemovedBlocks(): void
    {
        $block = new ViewModel();
        $this->blockRegistry->add('some-block', $block);

        $this->assertSame([], $this->blockRegistry->getRemovedBlocks());

        $this->blockRegistry->remove('some-block');
        $this->blockRegistry->remove('never-added');

        $this->assertSame(['some-block', 'never-added'], $this->blockRegistry->getRemovedBlocks());
    }

    public function testAnonymousBlockId(): void
    {
        $viewModel = new ViewModel();
        $child = new ViewModel();
        $viewModel->addChild($child);
        $this->blockRegistry->add('test.block', $viewModel);

        $this->assertEquals(
            'anonymous.content.1',
            $child->getVariable('blockId'),
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
