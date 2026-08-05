<?php

declare(strict_types=1);

namespace ConLayout;

use InvalidArgumentException;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\View\Model\ModelInterface;

class BlockManager extends AbstractPluginManager
{
    /**
     * @param ModelInterface $plugin
     * @throws InvalidArgumentException
     */
    public function validate($plugin): void
    {
        if ($plugin instanceof ModelInterface) {
            return;
        }
        throw new InvalidServiceException(sprintf(
            'Plugin of type %s is invalid; must implement Laminas\View\Model\ModelInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
        ));
    }

    public function validatePlugin($instance): void
    {
        try {
            $this->validate($instance);
        } catch (InvalidServiceException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
