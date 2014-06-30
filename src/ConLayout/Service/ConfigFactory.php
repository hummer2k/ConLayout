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
    use \ConLayout\OptionTrait;
    
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
        
        $handleBehavior = $this->getOption($appConfig, 'con-layout/handle_behavior', 'controller_action');
        $enableCache = $this->getOption($appConfig, 'con-layout/enable_cache', false);

        $routeMatch = $router->match($request);
        $actionHandles = $this->getActionHandles($routeMatch, $handleBehavior);
        
        $config = new Config(
            $serviceLocator->get('ConLayout\Service\Config\CollectorInterface'),
            $serviceLocator->get('ConLayout\Cache')
        );
        $config->addHandle($actionHandles);
        $config->setIsCacheEnabled($enableCache);
        return $config;
    }
    
    /**
     * retrieve handle for current action
     * 
     * @param \Zend\Mvc\Router\RouteMatch $routeMatch
     * @param string $handleBehavior
     * @return array
     */
    protected function getActionHandles(RouteMatch $routeMatch = null, $handleBehavior = null)
    {
        if (null === $routeMatch) {
            return array();
        }
        $routeName = $routeMatch->getMatchedRouteName();
        $namespace = $routeMatch->getParam('__NAMESPACE__');
        $controller = $routeMatch->getParam('controller');
        if (null !== $namespace) {
            $controller = $namespace . '\\' . $controller;
        }
        $controller = strtolower($controller);
        $action = strtolower($routeMatch->getParam('action'));
        
        switch ($handleBehavior) {
        case 'routematch':
            return array($routeName);
        case 'combined':
            return array(
                $routeName,
                $controller,
                $controller . '::' . $action,
            );
        case 'controller_action':
        default:
            return array(
                $controller,
                $controller . '::' . $action
            );
        }
    }
}
