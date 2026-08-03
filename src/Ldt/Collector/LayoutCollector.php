<?php

declare(strict_types=1);

namespace ConLayout\Ldt\Collector;

use ConLayout\Layout\LayoutInterface;
use ConLayout\Options\ModuleOptions;
use ConLayout\Updater\Collector\FilesystemCollector;
use ConLayout\Updater\LayoutUpdaterInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Resolver\ResolverInterface;
use Laminas\DeveloperTools\Collector\AbstractCollector;

/**
 * Collector for LaminasDeveloperToolbar
 *
 */
class LayoutCollector extends AbstractCollector
{
    public const NAME = 'con-layout';

    protected LayoutInterface $layout;

    protected LayoutUpdaterInterface $updater;

    protected ResolverInterface $viewResolver;

    private ModuleOptions $moduleOptions;

    private FilesystemCollector $filesystemCollector;

    public function __construct(
        LayoutInterface $layout,
        LayoutUpdaterInterface $updater,
        ResolverInterface $viewResolver,
        ModuleOptions $moduleOptions,
        FilesystemCollector $filesystemCollector
    ) {
        $this->layout  = $layout;
        $this->updater = $updater;
        $this->viewResolver = $viewResolver;
        $this->moduleOptions = $moduleOptions;
        $this->filesystemCollector = $filesystemCollector;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getPriority(): int
    {
        return 600;
    }

    /**
     * collect data for zdt
     */
    public function collect(MvcEvent $mvcEvent): static
    {
        $layout = $mvcEvent->getViewModel();
        $blocks = [];
        foreach ($this->layout->getBlocks() as $blockId => $block) {
            if ($parentBlock = $block->getOption('parent')) {
                $captureTo = $parentBlock . '::' . $block->captureTo();
            } else {
                $captureTo = $block->captureTo();
            }
            $blocks[$blockId] = [
                'instance' => $block,
                'template' => $this->resolveTemplate($block->getTemplate()),
                'capture_to' => $captureTo,
                'class' => get_class($block)
            ];
        }
        $this->data = [
            'handles' => $this->updater->getHandles(true),
            'collected_files' => $this->resolveCollectedFiles($this->filesystemCollector->getCollectedFiles()),
            'layout_structure' => $this->updater->getLayoutStructure()->toArray(),
            'blocks' => $blocks,
            'layout_template' => $layout->getTemplate(),
            'current_area' => $this->updater->getArea(),
            'remote_call' => $this->moduleOptions->getRemoteCall()
        ];
        return $this;
    }

    /**
     * retrieve resolved template path
     */
    private function resolveTemplate(string $template): string
    {
        return $this->getFilepath($this->viewResolver->resolve($template));
    }

    private function resolveCollectedFiles(array $collectedFiles): array
    {
        foreach ($collectedFiles as &$files) {
            foreach ($files as &$file) {
                $file = $this->getFilepath(realpath($file));
            }
        }
        return $collectedFiles;
    }

    private function getFilepath(string $file): string
    {
        return str_replace(getcwd(), '', $file);
    }

    public function getLayoutTemplate(): string
    {
        return $this->data['layout_template'];
    }

    public function getCurrentArea(): string
    {
        return $this->data['current_area'];
    }

    public function getHandles(): array
    {
        return $this->data['handles'];
    }

    public function getLayoutStructure(): array
    {
        return $this->data['layout_structure'];
    }

    public function getBlocks(): array
    {
        return $this->data['blocks'];
    }

    public function getRemoteCall(): bool|string
    {
        return $this->data['remote_call'];
    }

    public function getCollectedFiles(): array
    {
        return $this->data['collected_files'];
    }

    public function getRemoteCallUrl(string $file): string
    {
        if ($remoteCall = $this->getRemoteCall()) {
            return 'http://' . $remoteCall . '?message=' . $file;
        }
        return $file;
    }
}
