<?php

namespace ConLayout\Layout;

use Zend\View\Model\ModelInterface;
use Zend\View\Model\ViewModel;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
interface LayoutInterface
{  
    const BLOCK_NAME_ROOT = 'root';

    /**
     * 
     * @param string $blockId
     * @return ModelInterface
     */
    public function getBlock($blockId, $recursive = false);

    /**
     *
     * @return ModelInterface[]
     */
    public function getBlocks();

    /**
     *
     * @param string $blockId
     * @param ViewModel $block
     * @param string $parentId
     */
    public function addBlock($blockId, ViewModel $block);

    /**
     *
     * @param string $blockId
     */
    public function removeBlock($blockId);

    /**
     *
     * @param ModelInterface $root
     */
    public function injectBlocks(ModelInterface $root = null);
}
