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
    const BLOCK_ID_ROOT = 'root';

    /**
     * retrieve single block by block id
     *
     * @param string $blockId
     * @return ModelInterface
     */
    public function getBlock($blockId);

    /**
     * retrieve all blocks
     *
     * @return ModelInterface[]
     */
    public function getBlocks();

    /**
     * add a single block
     *
     * @param string $blockId
     * @param ViewModel $block
     * @param string $parentId
     */
    public function addBlock($blockId, ViewModel $block);

    /**
     * removes a single block
     *
     * @param string $blockId
     */
    public function removeBlock($blockId);

    /**
     * load the layout
     */
    public function load();

    /**
     * set root view model/layout
     *
     * @param ModelInterface $root
     */
    public function setRoot(ModelInterface $root);
}
