<?php

namespace ConLayout;

use Zend\View\Model\ModelInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
interface LayoutInterface
{
    /**
     * 
     * @param string $blockId
     * @return ModelInterface
     */
    public function getBlock($blockId);

    /**
     *
     * @return ModelInterface[]
     */
    public function getBlocks();

    /**
     *
     * @param string $blockId
     * @param ModelInterface $block
     */
    public function addBlock($blockId, ModelInterface $block);

    /**
     *
     * @param string $blockId
     */
    public function removeBlock($blockId);

    /**
     *
     * @param ModelInterface $root
     */
    public function injectBlocks(ModelInterface $root);
}
