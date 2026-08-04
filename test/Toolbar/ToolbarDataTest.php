<?php

declare(strict_types=1);

namespace ConLayoutTest\Toolbar;

use ConLayout\Block\BlockPoolInterface;
use ConLayout\Handle\Handle;
use ConLayout\LayoutManager;
use ConLayout\Options\ModuleOptions;
use ConLayout\Toolbar\ToolbarData;
use ConLayout\Updater\Collector\FilesystemCollector;
use Laminas\Config\Config;
use Laminas\View\Model\ViewModel;
use PHPUnit\Framework\TestCase;

class ToolbarDataTest extends TestCase
{
    public function testHasLayoutAndTemplate(): void
    {
        $root = $this->getRoot();

        $data = new ToolbarData($this->getLayoutManager($root));

        $this->assertTrue($data->hasLayout());
        $this->assertSame('base', $data->getLayoutTemplate());
    }

    public function testNoLayoutWithoutRoot(): void
    {
        $layoutManager = $this->createMock(LayoutManager::class);
        $layoutManager->method('getRoot')->willReturn(false);

        $data = new ToolbarData($layoutManager);

        $this->assertFalse($data->hasLayout());
        $this->assertSame('', $data->getLayoutTemplate());
        $this->assertSame([], $data->getBlocks());
    }

    public function testGetHandlesAndArea(): void
    {
        $layoutManager = $this->createMock(LayoutManager::class);
        $layoutManager->method('getHandles')->willReturn([
            new Handle('default', -1),
            new Handle('home', 10),
        ]);
        $layoutManager->method('getArea')->willReturn('frontend');

        $data = new ToolbarData($layoutManager);

        $handles = $data->getHandles();
        $this->assertCount(2, $handles);
        $this->assertSame('default', (string) $handles[0]);
        $this->assertSame(-1, $handles[0]->getPriority());
        $this->assertSame('frontend', $data->getArea());
    }

    public function testGetUpdateFiles(): void
    {
        $layoutManager = $this->createMock(LayoutManager::class);
        $collector = $this->createMock(FilesystemCollector::class);
        $collector->method('getCollectedFiles')->willReturn([
            'default' => ['/path/to/default.xml'],
        ]);

        $data = new ToolbarData($layoutManager, $collector);

        $this->assertSame(['default' => ['/path/to/default.xml']], $data->getUpdateFiles());
    }

    public function testGetUpdateFilesWithoutCollector(): void
    {
        $layoutManager = $this->createMock(LayoutManager::class);

        $data = new ToolbarData($layoutManager);

        $this->assertSame([], $data->getUpdateFiles());
    }

    public function testGetModuleOptions(): void
    {
        $layoutManager = $this->createMock(LayoutManager::class);
        $moduleOptions = new ModuleOptions([
            'enabled'          => false,
            'layout_template'  => 'layout',
            'default_area'     => 'backend',
            'debug'            => true,
            'base_path'        => '/base/',
            'body_class_prefix' => 'page-',
        ]);

        $data = new ToolbarData($layoutManager, null, null, $moduleOptions);

        $options = $data->getModuleOptions();
        $this->assertFalse($options['enabled']);
        $this->assertSame('layout', $options['layout_template']);
        $this->assertSame('backend', $options['default_area']);
        $this->assertTrue($options['debug']);
        $this->assertSame('/base/', $options['base_path']);
        $this->assertSame('page-', $options['body_class_prefix']);
    }

    public function testGetStructureSummaryAndHelpers(): void
    {
        $layoutManager = $this->createMock(LayoutManager::class);
        $layoutManager->method('getLayoutStructure')->willReturn(new Config([
            'blocks'  => ['root' => []],
            'helpers' => ['headLink' => [], 'headScript' => []],
        ], true));

        $data = new ToolbarData($layoutManager);

        $this->assertSame([
            'blocks'     => 1,
            'references' => 0,
            'helpers'    => 2,
            'includes'   => 0,
        ], $data->getStructureSummary());
        $this->assertSame(['headLink', 'headScript'], $data->getHelpers());
    }

    public function testGetBlocksWithDetails(): void
    {
        $root = $this->getRoot();

        $header = new ViewModel();
        $header->setOption('block_id', 'header');
        $header->setTemplate('layout::header');
        $header->setCaptureTo('header');
        $header->setOption('order', 10);
        $header->setOption('parent', 'root');
        $header->setOption('container', '<div class="wrap">%s</div>');
        $header->setAppend(false);
        $root->addChild($header);

        $data = new ToolbarData($this->getLayoutManager($root));

        $blocks = $data->getBlocks();
        $this->assertCount(1, $blocks);

        $rootData = $blocks[0];
        $this->assertSame('root', $rootData['id']);
        $this->assertSame('base', $rootData['template']);
        $this->assertFalse($rootData['anonymous']);
        $this->assertCount(1, $rootData['children']);

        $headerData = $rootData['children'][0];
        $this->assertSame('header', $headerData['id']);
        $this->assertSame('header', $headerData['capture_to']);
        $this->assertSame(10, $headerData['order']);
        $this->assertSame('root', $headerData['parent']);
        $this->assertSame('<div class="wrap">%s</div>', $headerData['container']);
        $this->assertFalse($headerData['append']);
        $this->assertSame([], $headerData['children']);
    }

    public function testGetBlocksMarksAnonymous(): void
    {
        $root = new ViewModel();
        $root->setOption('block_id', 'root');
        $root->setTemplate('base');

        $anonymous = new ViewModel();
        $anonymous->setOption('block_id', 'anonymous.content.1');
        $root->addChild($anonymous);

        $data = new ToolbarData($this->getLayoutManager($root));

        $this->assertTrue($data->getBlocks()[0]['children'][0]['anonymous']);
    }

    public function testGetUnrenderedBlocks(): void
    {
        $root = $this->getRoot();
        $header = new ViewModel();
        $header->setOption('block_id', 'header');
        $root->addChild($header);

        $layoutManager = $this->createMock(LayoutManager::class);
        $layoutManager->method('getRoot')->willReturn($root);
        $layoutManager->method('getBlocks')->willReturn([
            'root'     => $root,
            'header'   => $header,
            'orphan'   => new ViewModel(),
            'orphan-2' => new ViewModel(),
        ]);

        $blockPool = $this->createMock(BlockPoolInterface::class);
        $blockPool->method('getRemovedBlocks')->willReturn(['removed-block']);

        $data = new ToolbarData($layoutManager, null, $blockPool);

        $unrendered = $data->getUnrenderedBlocks();
        $this->assertSame(['orphan', 'orphan-2'], $unrendered['pool']);
        $this->assertSame(['removed-block'], $unrendered['removed']);
    }

    protected function getRoot(): ViewModel
    {
        $root = new ViewModel();
        $root->setOption('block_id', 'root');
        $root->setTemplate('base');
        return $root;
    }

    protected function getLayoutManager(ViewModel $root): LayoutManager
    {
        $layoutManager = $this->createMock(LayoutManager::class);
        $layoutManager->method('getRoot')->willReturn($root);
        return $layoutManager;
    }
}
