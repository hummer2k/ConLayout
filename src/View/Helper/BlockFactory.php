<?php
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\View\Helper;

use ConLayout\Block\BlockPoolInterface;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class BlockFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Block
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new Block(
            $container->get(BlockPoolInterface::class)
        );
    }
}
