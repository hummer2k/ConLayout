<?php

namespace ConLayoutTest\View\Helper;

use ConLayout\Block\BlockPoolInterface;
use ConLayout\View\Helper\Block;
use ConLayout\View\Helper\BlockFactory;
use ConLayoutTest\AbstractTest;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Helper\AbstractHelper;
use Zend\View\HelperPluginManager;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockFactoryTest extends AbstractTest
{
    public function testFactory()
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
