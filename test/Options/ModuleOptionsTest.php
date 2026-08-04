<?php

declare(strict_types=1);

namespace ConLayoutTest\Options;

use ConLayout\Options\ModuleOptions;
use ConLayoutTest\AbstractTestCase;

class ModuleOptionsTest extends AbstractTestCase
{
    public function testDefaults(): void
    {
        $moduleOptions = new ModuleOptions();
        $this->assertIsArray($moduleOptions->getViewHelpers());
        $this->assertIsString($moduleOptions->getCacheBusterInternalBaseDir());
        $this->assertIsArray($moduleOptions->getLayoutUpdatePaths());
        $this->assertIsArray($moduleOptions->getLayoutUpdateExtensions());
        $this->assertIsArray($moduleOptions->getBlockDefaults());
        $this->assertIsString($moduleOptions->getDefaultArea());
        $this->assertTrue($moduleOptions->isEnabled());
        $this->assertSame('', $moduleOptions->getLayoutTemplate());
        $this->assertSame('', $moduleOptions->getBasePath());
    }

    public function testSettersGetters(): void
    {
        $moduleOptions = new ModuleOptions();

        $moduleOptions->setViewHelpers(['view_helper']);
        $this->assertEquals(['view_helper'], $moduleOptions->getViewHelpers());

        $moduleOptions->setCacheBusterInternalBaseDir('./my/path');
        $this->assertEquals('./my/path', $moduleOptions->getCacheBusterInternalBaseDir());

        $moduleOptions->setBlockDefaults(['class' => 'MyBlock']);

        $moduleOptions->setDefaultArea('default_area');
        $this->assertEquals('default_area', $moduleOptions->getDefaultArea());

        $moduleOptions->setEnabled(false);
        $this->assertFalse($moduleOptions->isEnabled());

        $moduleOptions->setLayoutTemplate('layout::default');
        $this->assertSame('layout::default', $moduleOptions->getLayoutTemplate());

        $moduleOptions->setBasePath('/my/base');
        $this->assertSame('/my/base', $moduleOptions->getBasePath());

        $this->assertSame([
            'class' => 'MyBlock'
        ], $moduleOptions->getBlockDefaults());
    }

    public function testSetLayoutUpdateExtensions(): void
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
