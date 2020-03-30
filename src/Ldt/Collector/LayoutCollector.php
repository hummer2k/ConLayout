<?php

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
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutCollector extends AbstractCollector
{
    public const NAME = 'con-layout';

    /**
     *
     * @var LayoutInterface
     */
    protected $layout;

    /**
     *
     * @var LayoutUpdaterInterface
     */
    protected $updater;

    /**
     *
     * @var ResolverInterface
     */
    protected $viewResolver;

    /**
     * @var ModuleOptions
     */
    private $moduleOptions;

    /**
     * @var FilesystemCollector
     */
    private $filesystemCollector;

    /**
     *
     * @param LayoutInterface $layout
     * @param LayoutUpdaterInterface $updater
     * @param ResolverInterface $viewResolver
     * @param ModuleOptions $moduleOptions
     * @param FilesystemCollector $filesystemCollector
     */
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

    /**
     *
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     *
     * @return int
     */
    public function getPriority()
    {
        return 600;
    }

    /**
     * collect data for zdt
     *
     * @param MvcEvent $mvcEvent
     * @return LayoutCollector
     */
    public function collect(MvcEvent $mvcEvent)
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
     *
     * @param string $template
     * @return string
     */
    private function resolveTemplate(string $template): string
    {
        return $this->getFilepath($this->viewResolver->resolve($template));
    }

    /**
     * @param array $collectedFiles
     * @return array
     */
    private function resolveCollectedFiles(array $collectedFiles): array
    {
        foreach ($collectedFiles as &$files) {
            foreach ($files as &$file) {
                $file = $this->getFilepath(realpath($file));
            }
        }
        return $collectedFiles;
    }

    /**
     * @param string $file
     * @return string
     */
    private function getFilepath(string $file): string
    {
        return str_replace(getcwd(), '', $file);
    }

    /**
     *
     * @return string
     */
    public function getLayoutTemplate()
    {
        return $this->data['layout_template'];
    }

    /**
     *
     * @return string
     */
    public function getCurrentArea()
    {
        return $this->data['current_area'];
    }

    /**
     *
     * @return array
     */
    public function getHandles()
    {
        return $this->data['handles'];
    }

    /**
     *
     * @return array
     */
    public function getLayoutStructure()
    {
        return $this->data['layout_structure'];
    }

    /**
     *
     * @return array
     */
    public function getBlocks()
    {
        return $this->data['blocks'];
    }

    /**
     * @return string|false
     */
    public function getRemoteCall()
    {
        return $this->data['remote_call'];
    }

    /**
     * @return mixed
     */
    public function getCollectedFiles()
    {
        return $this->data['collected_files'];
    }

    /**
     * @param string $file
     * @return string
     */
    public function getRemoteCallUrl(string $file): string
    {
        if ($remoteCall = $this->getRemoteCall()) {
            return 'http://' . $remoteCall . '?message=' . $file;
        }
        return $file;
    }
}
