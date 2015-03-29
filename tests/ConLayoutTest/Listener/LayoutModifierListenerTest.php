<?php

namespace ConLayoutTest\Listener;

use ConLayout\Listener\LayoutModifierListener;
use ConLayout\Listener\LayoutModifierListenerFactory;
use ConLayoutTest\AbstractTest;
use Zend\EventManager\EventManager;
use Zend\View\Helper\HeadLink;
use Zend\View\Model\ViewModel;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutModifierListenerTest extends AbstractTest
{
    public function testFactory()
    {
        $instance = $this->getInstance();
        $this->assertInstanceOf(
            'ConLayout\Listener\LayoutModifierListener',
            $instance
        );
    }

    protected function getInstance()
    {
        $factory = new LayoutModifierListenerFactory();
        $instance = $factory->createService($this->sm);
        return $instance;
    }

    public function testApplyHelpers()
    {
        $layoutModifierListener = $this->getInstance();

        $helperConfig = [
            'headLink' => [
                '/css/styles.css'
            ],
            'headScript' => [
                [
                    '/js/main.js'
                ]
            ],
            'headTitle' => 'My Title'
        ];
        
        $layoutModifierListener->getLayoutService()->setLayoutConfig($helperConfig);

        $viewPlugins = $this->sm->get('viewHelperManager');

        $basePath =  $viewPlugins->get('basePath');
        $basePath->setBasePath('/');
        $headLink = $viewPlugins->get('headLink');
        $headTitle = $viewPlugins->get('headTitle');
        $headScript = $viewPlugins->get('headScript');

        $layoutModifierListener->applyHelpers(new \Zend\Mvc\MvcEvent());

        $this->assertEquals(
            file_get_contents(__DIR__ . '/../_files/styles.html'),
            $headLink->toString()
        );

        $this->assertEquals(
            '<title>My Title</title>',
            $headTitle->toString()
        );

        $basePath->setBasePath('/base_path');

        $layoutModifierListener->applyHelpers(new \Zend\Mvc\MvcEvent());

        $this->assertEquals(
            file_get_contents(__DIR__ . '/../_files/script.html'),
            $headScript->toString()
        );

    }

    public function testSetLayoutTemplateWhenEmpty()
    {
        $layoutModifierListener = $this->getInstance();
        $layoutModifierListener->getLayoutService()
            ->setLayoutConfig([
            'layout' => 'layout/1col'
        ]);
        $layout = new ViewModel();
        $layout->setTemplate('');

        $mvcEvent = new \Zend\Mvc\MvcEvent();
        $mvcEvent->setViewModel($layout);

        $layoutModifierListener->setLayoutTemplate($mvcEvent);

        $this->assertEquals('layout/1col', $layout->getTemplate());

    }

    public function testSetLayoutTemplateWhenAlreadySet()
    {
        $layoutModifierListener = $this->getInstance();
        $layoutModifierListener->getLayoutService()
            ->setLayoutConfig([
            'layout' => 'layout/1col'
        ]);
        $layout = new ViewModel();
        $layout->setTemplate('layout/2cols-left');

        $mvcEvent = new \Zend\Mvc\MvcEvent();
        $mvcEvent->setViewModel($layout);

        $layoutModifierListener->setLayoutTemplate($mvcEvent);

        $this->assertEquals('layout/2cols-left', $layout->getTemplate());

    }

    public function testAttach()
    {
        $eventManager = new EventManager();
        $listener = $this->getInstance();

        $this->assertEquals(0, count($eventManager->getListeners(\Zend\Mvc\MvcEvent::EVENT_RENDER)));

        $listener->attach($eventManager);

        $this->assertEquals(3, count($eventManager->getListeners(\Zend\Mvc\MvcEvent::EVENT_RENDER)));
    }

    public function testAddBlocksToLayout()
    {
        $listener = $this->getInstance();

        $mvcEvent = new \Zend\Mvc\MvcEvent();
        $layout = new ViewModel();

        $contentViewModel = new ViewModel();
        $layout->addChild($contentViewModel);

        $this->assertCount(1, $layout->getChildren());

        $mvcEvent->setViewModel($layout);

        $listener->getLayoutService()->setLayoutConfig([
            'blocks' => [
                'header' => [
                    'header' => [
                        'template' => 'my/header/tpl'
                    ]
                ],
                'sidebarLeft' => [
                    'widget1' => [
                        'template' => 'my/widget'
                    ]
                ]
            ]
        ]);

        $listener->addBlocksToLayout($mvcEvent);

        $this->assertCount(3, $layout->getChildren());

    }


    public function testAddBlocksToLayoutTerminal()
    {
        $listener = $this->getInstance();

        $mvcEvent = new \Zend\Mvc\MvcEvent();
        $layout = new ViewModel();
        $layout->setTerminal(true);

        $mvcEvent->setViewModel($layout);

        $listener->getLayoutService()->setLayoutConfig([
            'blocks' => [
                'header' => [
                    'header' => [
                        'template' => 'my/header/tpl'
                    ]
                ],
                'sidebarLeft' => [
                    'widget1' => [
                        'template' => 'my/widget'
                    ]
                ]
            ]
        ]);

        $listener->addBlocksToLayout($mvcEvent);

        $this->assertCount(0, $layout->getChildren());
    }

    public function testAddCssWhenDebuugerIsEnabled()
    {
        $this->sm->get('ConLayout\Debugger')->setEnabled(true);

        $listener = $this->getInstance();

        /* @var $headLink HeadLink */
        $headLink = $this->sm->get('ViewHelperManager')->get('headLink');

        $mvcEvent = new \Zend\Mvc\MvcEvent();
        $mvcEvent->setViewModel(new ViewModel());

        $listener->addBlocksToLayout($mvcEvent);

        $found = false;
        foreach ($headLink->getContainer() as $css) {
            if ($css->href === LayoutModifierListener::DEBUG_CSS) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    public function testSetHelperConfig()
    {
        $listener = $this->getInstance();

        $helperConfig = $listener->getHelperConfig();

        $newHelperConfig = \Zend\Stdlib\ArrayUtils::merge(
            $helperConfig,
            [
                'headTitle' => [
                    'defaultMethod' => 'set'
                ]
            ]
        );

        $listener->setHelperConfig($newHelperConfig);
        $this->assertEquals($newHelperConfig, $listener->getHelperConfig());
        $listener->setHelperConfig($helperConfig);
        $this->assertEquals($helperConfig, $listener->getHelperConfig());
        
    }

}
