<?php

namespace ConLayout;

use ConLayout\Handle\HandleInterface;
use Zend\View\Model\ModelInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
interface LayoutManagerInterface
{
    /**
     * @param string $blockId
     * @return ModelInterface
     */
    public function getBlock($blockId);

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
     * @return ModelInterface[]
     */
    public function getBlocks();

    /**
     *
     * @param HandleInterface $handle
     */
    public function addHandle(HandleInterface $handle);

    /**
     * @return HandleInterface[]
     */
    public function getHandles();

    /**
     * @param string $handle name of handle to remove
     */
    public function removeHandle($handle);

    /**
     *
     * @param array $blockConfig
     */
    public function generateBlocks();

    /**
     *
     * @param ModelInterface $layout
     */
    public function injectBlocks(ModelInterface $layout);

    /**
     * 
     */
    public function sortBlocks();

    /**
     * 
     */
    public function loadLayout();
}
