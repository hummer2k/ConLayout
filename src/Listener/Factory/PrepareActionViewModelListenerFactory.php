<?php
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Listener\Factory;

use ConLayout\Block\BlockPoolInterface;
use ConLayout\Listener\PrepareActionViewModelListener;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PrepareActionViewModelListenerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return PrepareActionViewModelListener
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new PrepareActionViewModelListener(
            $container->get(BlockPoolInterface::class)
        );
    }
}
