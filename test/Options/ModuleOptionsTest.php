<?php

namespace ConLayoutTest\Options;

use ConLayout\Options\ModuleOptions;
use ConLayoutTest\AbstractTest;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ModuleOptionsTest extends AbstractTest
{
    public function testDefaults()
    {
        $moduleOptions = new ModuleOptions();
        $this->assertIsArray($moduleOptions->getControllerMap());
        $this->assertIsBool($moduleOptions->isPreferRouteMatchController());
        $this->assertIsArray($moduleOptions->getViewHelpers());
        $this->assertIsString($moduleOptions->getCacheBusterInternalBaseDir());
        $this->assertIsArray($moduleOptions->getLayoutUpdatePaths());
        $this->assertIsArray($moduleOptions->getLayoutUpdateExtensions());
        $this->assertIsArray($moduleOptions->getBlockDefaults());
        $this->assertIsArray($moduleOptions->getListeners());
        $this->assertIsString($moduleOptions->getDefaultArea());
    }

    public function testSettersGetters()
    {
        $moduleOptions = new ModuleOptions();

        $moduleOptions->setViewHelpers(['view_helper']);
        $this->assertEquals(['view_helper'], $moduleOptions->getViewHelpers());

        $moduleOptions->setCacheBusterInternalBaseDir('./my/path');
        $this->assertEquals('./my/path', $moduleOptions->getCacheBusterInternalBaseDir());

        $moduleOptions->setBlockDefaults(['class' => 'MyBlock']);

        $moduleOptions->setDefaultArea('default_area');
        $this->assertEquals('default_area', $moduleOptions->getDefaultArea());

        $listeners = ['listener' => true];
        $moduleOptions->setListeners($listeners);
        $this->assertSame($listeners, $moduleOptions->getListeners());

        $this->assertSame([
            'class' => 'MyBlock'
        ], $moduleOptions->getBlockDefaults());

        $controllerMap = [
            'MappedNs' => true,
            'ZendTest\MappedNs' => true
        ];
        $moduleOptions->setControllerMap($controllerMap);
        $this->assertEquals(
            $controllerMap,
            $moduleOptions->getControllerMap()
        );

        $this->assertEquals(false, $moduleOptions->isPreferRouteMatchController());
    }

    public function testSetLayoutUpdateExtensions()
    {
        $moduleOptions = new ModuleOptions();
        $layoutUpdateExtensions = [
            'php' => 'php',
            'xml' => false,
            'yaml'
        ];
        $moduleOptions->setLayoutUpdateExtensions($layoutUpdateExtensions);

        $this->assertEquals([
            'php' => 'php',
            'yaml' => 'yaml'
        ], $moduleOptions->getLayoutUpdateExtensions());
    }
}
