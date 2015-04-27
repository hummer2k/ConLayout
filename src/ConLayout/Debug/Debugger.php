<?php

namespace ConLayout\Debug;

use ConLayout\Layout\Layout;
use Zend\View\Model\ModelInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Debugger
{
    const VAR_BLOCK_NAME        = '__BLOCK_NAME__';
    const VAR_BLOCK_TPL         = '__BLOCK_TPL__';
    const VAR_BLOCK_CLASS       = '__BLOCK_CLASS__';
    const VAR_BLOCK_ORIGINAL    = '__BLOCK_ORIGINAL__';
    const VAR_BLOCK_CAPTURE_TO  = '__BLOCK_CAPTURE_TO__';
    const VAR_BLOCK_TYPE        = '__BLOCK_TYPE__';

    /**
     *
     * @param ModelInterface $block
     * @param string $captureTo
     * @return ModelInterface
     */
    public function addDebugBlock(ModelInterface $block, $parent, $captureTo)
    {
        $block->setCaptureTo('content');
        $debugBlock = clone $block;
        $debugBlock->setVariables([
            self::VAR_BLOCK_NAME => $block->getVariable(Layout::BLOCK_ID_VAR),
            self::VAR_BLOCK_TPL => $block->getTemplate(),
            self::VAR_BLOCK_CLASS => get_class($block),
            self::VAR_BLOCK_ORIGINAL => $block,
            self::VAR_BLOCK_CAPTURE_TO => $parent . Layout::CAPTURE_TO_DELIMITER . $captureTo
        ]);
        $debugBlock->setCaptureTo($captureTo);
        $debugBlock->setTemplate('blocks/debug');
        $debugBlock->addChild($block);
        return $debugBlock;
    }
}
