<?php
namespace ConLayout\Listener;

use Zend\ServiceManager\FactoryInterface,
    Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutModifierListenerFactory
    implements FactoryInterface
{
    use \ConLayout\OptionTrait;
    
    /**
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * @return LayoutModifierListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $viewRenderer = $serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $layout = $viewRenderer->viewModel()->getRoot(); 
        
        $helperConfig = $this->getOption($config, 'con-layout/helpers', array());
        $layoutModifierListener = new LayoutModifierListener(
            $serviceLocator->get('ConLayout\Service\LayoutService'),
            $serviceLocator->get('ConLayout\Service\BlocksBuilder'),
            $serviceLocator->get('ConLayout\Service\LayoutModifier'),
            $layout,
            $viewRenderer,
            $helperConfig
        );
        foreach ($helperConfig as $helper => $value) {
            if (is_array($value) && isset($value['valuePreparers'])) {
                foreach ($value['valuePreparers'] as $valuePreparer) {
                    $layoutModifierListener->addValuePreparer($helper, $serviceLocator->get($valuePreparer));
                }
                unset($helperConfig[$helper]['valuePreparers']);
            }
        }
        return $layoutModifierListener;
    }
}
