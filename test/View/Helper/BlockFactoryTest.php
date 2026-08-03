<?php

declare(strict_types=1);

namespace ConLayoutTest\View\Helper;

use ConLayout\Block\BlockPoolInterface;
use ConLayout\View\Helper\Block;
use ConLayout\View\Helper\BlockFactory;
use ConLayoutTest\AbstractTestCase;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\HelperPluginManager;

class BlockFactoryTest extends AbstractTestCase
{
    public function testFactory(): void
    {
        $sm = new ServiceManager();
        $sm->setService(BlockPoolInterface::class, $this->blockPool);
        $helperManager = new HelperPluginManager($this->sm);

        $factory = new BlockFactory();
        $helper = $factory($this->sm, Block::class);

        $this->assertInstanceOf(Block::class, $helper);
        $this->assertInstanceOf(AbstractHelper::class, $helper);
    }
}
