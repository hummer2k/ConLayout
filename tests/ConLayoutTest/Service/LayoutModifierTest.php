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

    public function testFactory()
    {
        $factory = new \ConLayout\Service\LayoutModifierFactory();
        $this->assertInstanceOf(
            'ConLayout\Service\LayoutModifier',
            $factory->createService($this->sm)
        );
    }

    public function testAddBlocksToLayout()
    {
        $this->layoutService->reset();
        $layout = new ViewModel();
        $layoutModifier = $this->getLayoutModifier();
        $blocksBuilder = $this->getBlocksBuilder();
        $createdBlocks = $blocksBuilder->createBlocks(
            $this->layoutService->getBlockConfig()
        );
        
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
        $createdBlocks = $blocksBuilder->createBlocks(
            $this->layoutService->getBlockConfig()
        );
        
        $layoutModifier = $this->getLayoutModifier();
        $layoutModifier->addBlocksToLayout(
            $createdBlocks,
            $layout
        );
        $this->assertEquals(4, $layout->count());
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
        $createdBlocks = $blocksBuilder->createBlocks(
            $this->layoutService->getBlockConfig()
        );

        $layoutModifier = $this->getLayoutModifier();
        $layoutModifier->addBlocksToLayout(
            $createdBlocks,
            $layout
        );

        $this->assertEquals(0, $layout->count());

        $this->getEventManager()->getSharedManager()->attach(
            'ConLayout\Service\LayoutModifier', 'isAllowed', function($e) {
            return true;
        });
    }

    public function testSortBlocks()
    {
        $layoutModifier = $this->getLayoutModifier();
        $blockConfig = [
            'sidebar' => [
                'widget1' => [
                    'options' => [
                        'order' => 10
                    ]
                ],
                'widget2' => [
                    'options' => [
                        'order' => 4
                    ]
                ],
                'widget3' => [
                    'template' => 'asdf'
                ],
                'widget4' => [
                    'options' => [
                        'order' => -10
                    ]
                ],
                'widget5' => [
                    'template' => 'tmpl'
                ]
            ]
        ];
        $blocksBuilder = $this->sm->get('ConLayout\Service\BlocksBuilder');
        $createdBlocks = $blocksBuilder->createBlocks($blockConfig);

        $layout = new ViewModel();

        $layoutModifier->addBlocksToLayout($createdBlocks, $layout);

        $expectedOrder = [
            1 => 'widget4',
            2 => 'widget5',
            3 => 'widget3',
            4 => 'widget2',
            5 => 'widget1'
        ];

        $i = 1;
        foreach ($layout->getChildrenByCaptureTo('sidebar') as $child) {
            $this->assertEquals($expectedOrder[$i], $child->getVariable('nameInLayout'));
            $i++;
        }
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
