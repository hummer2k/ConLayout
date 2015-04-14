<?php

namespace ConLayout\Debug;

use Zend\View\Model\ModelInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Debugger
{
    /**
     *
     * @var boolean
     */
    protected $isEnabled = false;

    /**
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     *
     * @param boolean $flag
     * @return \ConLayout\Debugger
     */
    public function setEnabled($flag = true)
    {
        $this->isEnabled = (bool) $flag;
        return $this;
    }

    /**
     *
     * @param ModelInterface $block
     * @param string $captureTo
     * @return ModelInterface
     */
    public function addDebugBlock(ModelInterface $block, $captureTo)
    {
        $block->setCaptureTo('content');
        $debugBlock = clone $block;
        $debugBlock->setVariables(array(
            'blockName' => $block->getVariable('__BLOCK_ID__'),
            'blockTemplate' => $block->getTemplate(),
            'blockClass' => get_class($block),
            'originalBlock' => $block,
            'captureTo' => $captureTo
        ));
        $debugBlock->setCaptureTo($captureTo);
        $debugBlock->setTemplate('blocks/debug');
        $debugBlock->addChild($block);
        return $debugBlock;
    }
}
