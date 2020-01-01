<?php

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Filter;

use Laminas\Filter\FilterInterface;
use Laminas\View\Helper\ViewModel;

class DebugFilter implements FilterInterface
{
    /**
     * @var ViewModel
     */
    private $viewModelHelper;

    /**
     * DebugFilter constructor.
     * @param ViewModel $viewModelHelper
     */
    public function __construct(ViewModel $viewModelHelper)
    {
        $this->viewModelHelper = $viewModelHelper;
    }

    /**
     * @inheritDoc
     */
    public function filter($value)
    {
        if (!$block = $this->viewModelHelper->getCurrent()) {
            return $value;
        }
        if ($blockId = $block->getOption('block_id')) {
            $value = sprintf(
                '<!--[%s] [%s]-->%s<!--[/%s]-->',
                $blockId,
                get_class($block),
                $value,
                $blockId
            );
        }
        return $value;
    }
}
