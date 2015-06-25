<?php

namespace ConLayout;

use ConLayout\Block\BlockInterface;
use InvalidArgumentException;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\View\Model\ModelInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockManager extends AbstractPluginManager
{
    /**
     *
     * @param BlockInterface $plugin
     * @return void
     * @throws InvalidArgumentException
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof BlockInterface || $plugin instanceof ModelInterface) {
            return;
        }
        throw new InvalidArgumentException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Block\BlockInterface or Zend\View\Model\ModelInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
