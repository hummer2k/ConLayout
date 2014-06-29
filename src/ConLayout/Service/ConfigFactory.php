<?php
namespace ConLayout\Service;

use Zend\ServiceManager\FactoryInterface,
    Zend\Mvc\Router\RouteMatch,
    Zend\Mvc\Router\RouteStackInterface as Router;

/**
 * ConfigFactory
 *
 * @author hummer 
 */
class ConfigFactory
    implements FactoryInterface
{
    /**
     * 
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \ConLayout\Service\Config
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        /* @var $router Router */
        $router     = $serviceLocator->get('router');
        $request    = $serviceLocator->get('request');
        $appConfig  = $serviceLocator->get('Config');
        
        $handleBehavior = isset($appConfig['con-layout']['handle_behavior'])
            ? $appConfig['con-layout']['handle_behavior']
            : 'controller_action';
        
        $routeMatch = $router->match($request);
        $actionHandle = $this->getActionHandle($routeMatch, $handleBehavior);
        
        $config = new Config(
            $serviceLocator->get('ConLayout\Service\Config\CollectorInterface'),
            $serviceLocator->get('ConLayout\Cache')
        );
        $config->addHandle($actionHandle);
        return $config;
    }
    
    /**
     * get handle for current action
     * 
     * @param \Zend\Mvc\Router\RouteMatch $routeMatch
     * @param string $handleBehavior
     * @return string
     */
    protected function getActionHandle(RouteMatch $routeMatch = null, $handleBehavior = null)
    {
        if (null === $routeMatch) {
            return;
        }
        switch ($handleBehavior) {
        case 'routematch':
            return $routeMatch->getMatchedRouteName();
        case 'controller_action':
        default:
            $namespace = $routeMatch->getParam('__NAMESPACE__');
            $controller = $routeMatch->getParam('controller');
            if (null !== $namespace) {
                $controller = str_replace('\\', '_', $namespace) . '_' . $controller;
            }
            return strtolower($controller . '::' . $routeMatch->getParam('action'));
        }       
    }
}
