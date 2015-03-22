<?php

namespace ConLayout;

use Zend\View\Model\ViewModel;

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
     * @param ViewModel $block
     * @param string $captureTo
     * @return ViewModel
     */
    public function addDebugBlock(ViewModel $block, $captureTo)
    {
        $debugBlock = clone $block;
        $debugBlock->setVariables(array(
            'blockName' => $block->getVariable('nameInLayout'),
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
