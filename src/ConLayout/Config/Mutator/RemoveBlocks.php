<?php

namespace ConLayout\Config\Mutator;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class RemoveBlocks extends AbstractMutator
{
    const DIRECTIVE = '_remove';

    /**
     *
     * @param array $blockConfig
     * @return array
     */
    public function mutate(array $blockConfig)
    {
        if (isset($blockConfig[self::DIRECTIVE])) {
            $blockConfig = $this->removeBlocks($blockConfig, $blockConfig[self::DIRECTIVE]);
            unset($blockConfig[self::DIRECTIVE]);
        }
        return $blockConfig;
    }

    /**
     *
     * @param array $blockConfig
     * @param array|string $blocksToRemove
     * @return array
     */
    protected function removeBlocks(array $blockConfig, $blocksToRemove)
    {
        if (!is_array($blocksToRemove)) {
            $blocksToRemove = array($blocksToRemove => true);
        }
        foreach($blockConfig as $directiveOrCaptureTo => &$blocks) {
            if ($this->isDirective($directiveOrCaptureTo)) continue;
            foreach ($blocks as $blockName => &$block) {
                if (isset($block['children'])) {
                    $block['children'] = $this->removeBlocks($block['children'], $blocksToRemove);
                }
                foreach ($blocksToRemove as $removeBlock => $remove) {
                    if (false !== $remove && $blockName === $removeBlock) {
                        unset($blockConfig[$directiveOrCaptureTo][$blockName]);
                    }
                }
            }
        }
        return $blockConfig;
    }
}
