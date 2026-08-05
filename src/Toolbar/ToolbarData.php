<?php

declare(strict_types=1);

namespace ConLayout\Toolbar;

use ConLayout\Block\BlockRegistryInterface;
use ConLayout\Handle\HandleInterface;
use ConLayout\LayoutManager;
use ConLayout\Options\ModuleOptions;
use ConLayout\Updater\Collector\FilesystemCollector;
use Laminas\Config\Config;
use Laminas\View\Model\ModelInterface;

class ToolbarData
{
    public function __construct(
        private LayoutManager $layoutManager,
        private ?FilesystemCollector $filesystemCollector = null,
        private ?BlockRegistryInterface $blockPool = null,
        private ?ModuleOptions $moduleOptions = null,
    ) {}

    /**
     * @return HandleInterface[]
     */
    public function getHandles(): array
    {
        return $this->layoutManager->getHandles(true);
    }

    public function hasLayout(): bool
    {
        $root = $this->layoutManager->getRoot();
        return $root instanceof ModelInterface && '' !== (string) $root->getTemplate();
    }

    public function getLayoutTemplate(): string
    {
        $root = $this->layoutManager->getRoot();
        return $root instanceof ModelInterface ? (string) $root->getTemplate() : '';
    }

    public function getArea(): string
    {
        return $this->layoutManager->getArea();
    }

    /**
     * Layout update files applied per handle.
     *
     * @return array<string, array<int, string>>
     */
    public function getUpdateFiles(): array
    {
        if (null === $this->filesystemCollector) {
            return [];
        }
        return $this->filesystemCollector->getCollectedFiles();
    }

    /**
     * Snapshot of the relevant module options.
     *
     * @return array<string, mixed>
     */
    public function getModuleOptions(): array
    {
        if (null === $this->moduleOptions) {
            return [];
        }
        return [
            'enabled'                => $this->moduleOptions->isEnabled(),
            'layout_template'        => $this->moduleOptions->getLayoutTemplate(),
            'default_area'           => $this->moduleOptions->getDefaultArea(),
            'debug'                  => $this->moduleOptions->isDebug(),
            'base_path'              => $this->moduleOptions->getBasePath(),
            'body_class_prefix'      => $this->moduleOptions->getBodyClassPrefix(),
            'error_handle_attribute' => $this->moduleOptions->getErrorHandleAttribute(),
            'remote_call'            => $this->moduleOptions->getRemoteCall(),
            'layout_update_paths'    => $this->moduleOptions->getLayoutUpdatePaths(),
            'view_helpers'           => array_keys($this->moduleOptions->getViewHelpers()),
        ];
    }

    /**
     * Counts of the layout instructions in the merged layout structure.
     *
     * @return array<string, int>
     */
    public function getStructureSummary(): array
    {
        $structure = $this->layoutManager->getLayoutStructure();
        return [
            'blocks'     => $this->countInstructions($structure, 'blocks'),
            'references' => $this->countInstructions($structure, 'reference'),
            'helpers'    => $this->countInstructions($structure, 'helpers'),
            'includes'   => $this->countInstructions($structure, 'include'),
        ];
    }

    /**
     * Names of the view helpers invoked via the helpers instructions.
     *
     * @return array<int, string>
     */
    public function getHelpers(): array
    {
        $structure = $this->layoutManager->getLayoutStructure();
        $helpers = $structure->get('helpers');
        if (null === $helpers) {
            return [];
        }
        return array_keys($helpers->toArray());
    }

    /**
     * Rendered blocks as a nested tree, each entry containing the block
     * details and its children.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getBlocks(): array
    {
        $root = $this->layoutManager->getRoot();
        if (!$root instanceof ModelInterface) {
            return [];
        }

        $blocks = [];
        $this->collect($root, $blocks);
        return $blocks;
    }

    /**
     * Blocks registered in the pool but not part of the rendered tree,
     * and block ids that were removed.
     *
     * @return array{pool: array<int, string>, removed: array<int, string>}
     */
    public function getUnrenderedBlocks(): array
    {
        $poolIds = array_keys($this->layoutManager->getBlocks());
        $renderedIds = [];
        $this->collectRenderedIds($this->getBlocks(), $renderedIds);

        return [
            'pool'    => array_values(array_diff($poolIds, $renderedIds)),
            'removed' => $this->blockPool instanceof BlockRegistryInterface
                ? $this->blockPool->getRemovedBlocks()
                : [],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $blocks
     * @param array<int, string> $ids
     */
    private function collectRenderedIds(array $blocks, array &$ids): void
    {
        foreach ($blocks as $block) {
            $ids[] = (string) $block['id'];
            $this->collectRenderedIds($block['children'], $ids);
        }
    }

    /**
     * @param array<int, array<string, mixed>> $blocks
     */
    private function collect(ModelInterface $model, array &$blocks): void
    {
        $blockId = $model->getVariable('block_id');
        if (is_string($blockId) && '' !== $blockId) {
            $blocks[] = $this->getBlockData($blockId, $model);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function getBlockData(string $blockId, ModelInterface $model): array
    {
        $children = [];
        foreach ($model->getChildren() as $child) {
            $childId = $child->getVariable('block_id');
            if (is_string($childId) && '' !== $childId) {
                $children[] = $this->getBlockData($childId, $child);
            }
        }

        return [
            'id'         => $blockId,
            'template'   => (string) $model->getTemplate(),
            'capture_to' => (string) $model->captureTo(),
            'order'      => $this->readMetadata($model, 'order'),
            'parent'     => $this->readMetadata($model, 'parent'),
            'before'     => $this->readMetadata($model, 'before'),
            'after'      => $this->readMetadata($model, 'after'),
            'container'  => $this->readMetadata($model, 'container'),
            'wrapper'    => $this->readMetadata($model, 'wrapper'),
            'append'     => (bool) $model->isAppend(),
            'anonymous'  => str_starts_with($blockId, 'anonymous.'),
            'children'   => $children,
        ];
    }

    private function readMetadata(ModelInterface $model, string $name): mixed
    {
        $value = $model->getVariable($name);
        return null === $value || '' === $value ? null : $value;
    }

    private function countInstructions(Config $structure, string $instruction): int
    {
        $value = $structure->get($instruction);
        if (null === $value) {
            return 0;
        }
        return count($value->toArray());
    }
}
