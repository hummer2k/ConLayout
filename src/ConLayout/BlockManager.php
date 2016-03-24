<?php

namespace ConLayout;

use ConLayout\Block\BlockInterface;
use InvalidArgumentException;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\InvalidServiceException;
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
    public function validate($plugin)
    {
        if ($plugin instanceof BlockInterface || $plugin instanceof ModelInterface) {
            return;
        }
        throw new InvalidServiceException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Block\BlockInterface or Zend\View\Model\ModelInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }

    public function validatePlugin($instance)
    {
        try {
            $this->validate($instance);
        } catch (InvalidServiceException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
