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
        $this->assertInternalType('array', $moduleOptions->getAssetPreparers());
        $this->assertInternalType('array', $moduleOptions->getViewHelpers());
        $this->assertInternalType('string', $moduleOptions->getCacheBusterInternalBaseDir());
        $this->assertInternalType('array', $moduleOptions->getLayoutUpdatePaths());
        $this->assertInternalType('array', $moduleOptions->getLayoutUpdateExtensions());
        $this->assertFalse($moduleOptions->getEnableDebug());
        $this->assertInternalType('array', $moduleOptions->getBlockDefaults());
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

        $moduleOptions->setEnableDebug(true);
        $this->assertTrue($moduleOptions->getEnableDebug());

        $moduleOptions->setBlockDefaults(['class' => 'MyBlock']);

        $this->assertSame([
            'class' => 'MyBlock'
        ], $moduleOptions->getBlockDefaults());
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
