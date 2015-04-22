<?php
namespace ConLayout\Listener;

use ConLayout\Layout\LayoutInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ModelInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LoadLayoutListener
    implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;
            
    /**
     *
     * @var LayoutInterface
     */
    protected $layout;
    
    /**
     *
     * @var int
     */
    protected static $anonymousSuffix = 1;

    /**
     *
     * @param LayoutInterface $layout
     */
    public function __construct(LayoutInterface $layout)
    {
        $this->layout = $layout;
    }

    /**
     * 
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'loadLayout'));
    }
    
    /**
     * inject blocks to root ViewModel or layout if not flagged as terminal
     * 
     * @param MvcEvent $e
     */
    public function loadLayout(MvcEvent $e)
    {
        /* @var $root ModelInterface */
        $root = $e->getViewModel();
        if ($root->terminate()) {
            return;
        }
        $this->layout->setRoot($root);
        $this->layout->load();
    }
}
