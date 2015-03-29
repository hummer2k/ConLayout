<?php

namespace ConLayoutTest\Listener;

use ConLayout\Listener\LayoutModifierListenerFactory;
use ConLayoutTest\AbstractTest;

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
        
        $viewPlugins->get('basePath')->setBasePath('/');

        $layoutModifierListener->applyHelpers(new \Zend\Mvc\MvcEvent());

    }
}