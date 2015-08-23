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
        $this->assertInternalType('array', $moduleOptions->getExcludeActionHandleSegments());
        $this->assertInternalType('array', $moduleOptions->getAssetPreparers());
        $this->assertInternalType('array', $moduleOptions->getViewHelpers());
        $this->assertInternalType('string', $moduleOptions->getCacheBusterInternalBaseDir());
        $this->assertInternalType('array', $moduleOptions->getLayoutUpdatePaths());
        $this->assertInternalType('array', $moduleOptions->getLayoutUpdateExtensions());
        $this->assertInternalType('array', $moduleOptions->getBlockDefaults());
        $this->assertInternalType('string', $moduleOptions->getDefaultArea());
    }

    public function testSettersGetters()
    {
        $moduleOptions = new ModuleOptions();
        $moduleOptions->setAssetPreparers(['asset_preparer']);
        $this->assertEquals(['asset_preparer'], $moduleOptions->getAssetPreparers());

        $moduleOptions->setViewHelpers(['view_helper']);
        $this->assertEquals(['view_helper'], $moduleOptions->getViewHelpers());

        $moduleOptions->setCacheBusterInternalBaseDir('./my/path');
        $this->assertEquals('./my/path', $moduleOptions->getCacheBusterInternalBaseDir());

        $moduleOptions->setBlockDefaults(['class' => 'MyBlock']);

        $moduleOptions->setDefaultArea('default_area');
        $this->assertEquals('default_area', $moduleOptions->getDefaultArea());

        $this->assertSame([
            'class' => 'MyBlock'
        ], $moduleOptions->getBlockDefaults());

        $excludeActionHandleSegments = [
            'Controller',
            'Backend'
        ];
        $moduleOptions->setExcludeActionHandleSegments($excludeActionHandleSegments);
        $this->assertEquals(
            $excludeActionHandleSegments,
            $moduleOptions->getExcludeActionHandleSegments()
        );
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
