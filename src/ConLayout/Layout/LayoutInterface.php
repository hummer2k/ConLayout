<?php

namespace ConLayout\Layout;

use ConLayout\Generator\GeneratorInterface;
use Zend\View\Model\ModelInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
interface LayoutInterface
{
    const BLOCK_ID_ROOT = 'root';
    const BLOCK_ID_ACTION_RESULT = 'action.result';

    const CAPTURE_TO_DELIMITER = '::';

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
     * @param ModelInterface $block
     */
    public function addBlock($blockId, ModelInterface $block);

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

    /**
     * @param string $name
     * @param GeneratorInterface $generator
     * @param int $priority
     * @return mixed
     */
    public function attachGenerator($name, GeneratorInterface $generator, $priority = 1);

    /**
     * removes a generator
     *
     * @param string $name
     * @return mixed
     */
    public function detachGenerator($name);
}
