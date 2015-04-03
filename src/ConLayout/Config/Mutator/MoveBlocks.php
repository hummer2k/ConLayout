<?php

namespace ConLayout\Config\Mutator;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class MoveBlocks extends AbstractMutator
{
    const DIRECTIVE = '_move';

    protected $blocksToMove = [];

    public function mutate(array $config)
    {
        if (isset($config[self::DIRECTIVE])) {
            $config = $this->moveBlocks($config, $config[self::DIRECTIVE]);
            unset($config[self::DIRECTIVE]);
        }
        return $config;
    }

    protected function moveBlocks(array $config, $blocksToMove)
    {
        $movedBlocks = [];
        foreach ($blocksToMove as $blockNameToMove => $target) {
            if (false === strpos($target, '::')) {
                foreach ($config as $directiveOrCaptureTo => &$blocks) {
                    if ($this->isDirective($directiveOrCaptureTo)) continue;
                    foreach ($blocks as $blockName => &$block) {
                        if ($blockName === $blockNameToMove && !isset($movedBlocks[$blockName])) {
                            $config[$target][$blockName] = $block;
                            unset($config[$directiveOrCaptureTo][$blockName]);
                            $movedBlocks[$blockName] = true;
                        }
                    }
                }
            } else {
                list($targetBlock, $targetCaptureTo) = explode('::', $target);
                $blockToMove = $this->findBlockToMove($config, $blockNameToMove);
                $config = $this->moveBlockToTarget($config, $targetBlock, $targetCaptureTo, $blockNameToMove, $blockToMove);
            }
        }
        return $config;
    }

    protected function findBlockToMove(array &$config, $blockNameToMove)
    {
        foreach ($config as $captureTo => &$blocks) {
            if ($this->isDirective($captureTo)) continue;
            foreach ($blocks as $blockName => $block) {
                if (isset($block['children'])) {
                    $block = $this->findBlockToMove($block['children'], $blockNameToMove);
                }
                if ($blockName === $blockNameToMove) {
                    unset($blocks[$blockName]);
                    return $block;
                } 
            }
        }
        return $block;
    }

    protected function moveBlockToTarget(array $config, $targetBlock, $targetCaptureTo, $blockNameToMove, array $blockToMove)
    {
        foreach ($config as $captureTo => &$blocks) {
            if ($this->isDirective($captureTo)) continue;
            foreach ($blocks as $blockName => &$block) {
                if ($blockName === $targetBlock) {
                    $block['children'][$targetCaptureTo][$blockNameToMove] = $blockToMove;
                }
                if (isset($block['children'])) {
                    $block['children'] = $this->moveBlockToTarget($block['children'], $targetBlock, $targetCaptureTo, $blockNameToMove, $blockToMove);
                }
            }
        }
        return $config;
    }
}
