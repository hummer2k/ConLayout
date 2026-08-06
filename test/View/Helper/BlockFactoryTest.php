<?php

declare(strict_types=1);

namespace ConLayoutTest\View\Helper;

use ConLayout\Block\BlockRegistryInterface;
use ConLayout\View\Helper\Block;
use ConLayout\View\Helper\BlockFactory;
use ConLayoutTest\AbstractTestCase;
use Laminas\ServiceManager\ServiceManager;

class BlockFactoryTest extends AbstractTestCase
{
    public function testFactory(): void
    {
        $sm = new ServiceManager();
        $sm->setService(BlockRegistryInterface::class, $this->blockRegistry);

        $factory = new BlockFactory();
        $helper = $factory($sm, Block::class);

        $this->assertInstanceOf(Block::class, $helper);
    }
}
