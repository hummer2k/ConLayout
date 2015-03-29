<?php

namespace ConLayout\Config\Modifier;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class RemoveBlocks implements ModifierInterface
{
    /**
     *
     * @param array $blockConfig
     * @return array
     */
    public function modify(array $blockConfig)
    {
        if (isset($blockConfig['_remove'])) {
            $blockConfig = $this->removeBlocks($blockConfig, $blockConfig['_remove']);
            unset($blockConfig['_remove']);
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
        foreach($blockConfig as $captureTo => &$blocks) {
            if ($captureTo[0] === '_') continue;
            foreach ($blocks as $blockName => &$block) {
                if (isset($block['children'])) {
                    $block['children'] = $this->removeBlocks($block['children'], $blocksToRemove);
                }
                foreach ($blocksToRemove as $removeBlock => $remove) {
                    if (false !== $remove && $blockName === $removeBlock) {
                        unset($blockConfig[$captureTo][$blockName]);
                    }
                }
            }
        }
        return $blockConfig;
    }
}
