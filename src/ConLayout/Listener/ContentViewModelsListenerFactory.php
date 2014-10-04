<?php
namespace ConLayout\Listener;

use ConLayout\OptionTrait,
    Zend\ServiceManager\FactoryInterface,
    Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ContentViewModelsListenerFactory
    implements FactoryInterface
{
    use OptionTrait;
    
    /**
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * @return ContentViewModelsListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $contentViewModelsListener = new ContentViewModelsListener(
            $this->getOption($config, 'con-layout/content_capture_to', null)
        );
        return $contentViewModelsListener;
    }
}
