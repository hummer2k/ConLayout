<?php
namespace ConLayoutTest\Service;

use ConLayout\Debugger;
use ConLayout\Service\LayoutModifier;
use ConLayoutTest\AbstractTest;
use Zend\EventManager\EventManager;
use Zend\View\Model\ViewModel;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutModifierTest extends AbstractTest
{
    protected $em;

    public function testAddBlocksToLayout()
    {
        $this->layoutService->reset();
        $layout = new ViewModel();
        $layoutModifier = $this->getLayoutModifier();
        $blocksBuilder = $this->getBlocksBuilder();
        $blocksBuilder->setBlockConfig($this->layoutService->getBlockConfig());       
        $createdBlocks = $blocksBuilder->getCreatedBlocks();
        
        $layoutModifier->addBlocksToLayout(
            $createdBlocks,
            $layout
        ); 
        
        $this->assertEquals(1, $layout->count());
    }
    
    public function testAddBlocksToLayoutRouteHandle()
    {
        $this->layoutService->reset();
        $this->layoutService->addHandle(array('route', 'route/childroute'));        
        $layout = new ViewModel();
        $blocksBuilder = $this->getBlocksBuilder();
        $blocksBuilder->setBlockConfig($this->layoutService->getBlockConfig());       
        $createdBlocks = $blocksBuilder->getCreatedBlocks();
        
        $layoutModifier = $this->getLayoutModifier();
        $layoutModifier->addBlocksToLayout(
            $createdBlocks,
            $layout
        );
        $this->assertEquals(3, $layout->count());
    }

    public function testAcl()
    {
        $this->getEventManager()->getSharedManager()->attach(
            'ConLayout\Service\LayoutModifier', 'isAllowed', function($e) {
            return false;
        });

        $this->layoutService->reset();
        $this->layoutService->addHandle(array('route', 'route/childroute'));
        $layout = new ViewModel();
        $blocksBuilder = $this->getBlocksBuilder();
        $blocksBuilder->setBlockConfig($this->layoutService->getBlockConfig());
        $createdBlocks = $blocksBuilder->getCreatedBlocks();

        $layoutModifier = $this->getLayoutModifier();
        $layoutModifier->addBlocksToLayout(
            $createdBlocks,
            $layout
        );

        $this->assertEquals(0, $layout->count());
    }

    protected function getLayoutModifier()
    {
        $layoutModifier = new LayoutModifier(new Debugger());
        $layoutModifier->setEventManager($this->getEventManager());
        return $layoutModifier;
    }

    protected function getEventManager()
    {
        if (null === $this->em) {
            $this->em = new EventManager();
        }
        return $this->em;
    }
}
