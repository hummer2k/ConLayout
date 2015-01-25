<?php
namespace ConLayout\Listener;

use ConLayout\View\Helper\BodyClass,
    Zend\EventManager\AbstractListenerAggregate,
    Zend\EventManager\EventManagerInterface,
    Zend\Mvc\MvcEvent;
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BodyClassListener
    extends AbstractListenerAggregate
{
    /**
     *
     * @var BodyClass
     */
    protected $bodyClassHelper;
    
    /**
     * 
     * @param BodyClass $bodyClassHelper
     */
    public function __construct(BodyClass $bodyClassHelper)
    {
        $this->bodyClassHelper = $bodyClassHelper;
    }
    
    /**
     * 
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH,  array($this, 'addBodyClass'));
    }
    
    /**
     * 
     * @param MvcEvent $e
     * @return BodyClassListener
     */
    public function addBodyClass(MvcEvent $e) 
    {
        $helper = $this->bodyClassHelper;
        $routeMatchName = $e->getRouteMatch()->getMatchedRouteName();
        $className = preg_replace('#[^a-z0-9-]+#i', '-', $routeMatchName);
        $helper(strtolower($className));
        return $this;
    }
    
}