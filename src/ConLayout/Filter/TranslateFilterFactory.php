<?php

namespace ConLayout\Filter;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\MutableCreationOptionsInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class TranslateFilterFactory implements FactoryInterface, MutableCreationOptionsInterface
{
    /**
     *
     * @var array
     */
    private $options = [];

    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $translator = $serviceLocator->getServiceLocator()
            ->get('MvcTranslator');
        return new TranslateFilter($translator, $this->options);
    }

    /**
     *
     * @param array $options
     */
    public function setCreationOptions(array $options)
    {
        $this->options = $options;
    }
}
