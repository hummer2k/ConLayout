<?php

declare(strict_types=1);

namespace ConLayout;

use InvalidArgumentException;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\View\Model\ModelInterface;

/**
 * @template-extends AbstractPluginManager<ModelInterface>
 */
class BlockManager extends AbstractPluginManager
{
    public function validate(mixed $instance): void
    {
        if ($instance instanceof ModelInterface) {
            return;
        }
        throw new InvalidServiceException(sprintf(
            'Plugin of type %s is invalid; must implement Laminas\View\Model\ModelInterface',
            (is_object($instance) ? $instance::class : gettype($instance)),
        ));
    }

    public function validatePlugin(mixed $instance): void
    {
        try {
            $this->validate($instance);
        } catch (InvalidServiceException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
