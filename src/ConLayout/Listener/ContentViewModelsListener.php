<?php
namespace ConLayout\Listener;

use ConLayout\Debugger;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

/**
 * Listener to sort content view models
 * 
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ContentViewModelsListener
    implements  ListenerAggregateInterface
{
    use ListenerAggregateTrait;
    
    /**
     * view model returned from controller action
     * or created by zf via view model injection listener
     *
     * @var ViewModel
     */
    protected $actionResultViewModel;
    
    /**
     * content captureTo
     * 
     * @var string
     */
    protected $captureTo = 'content';

    /**
     *
     * @var Debugger
     */
    protected $debugger;
    
    /**
     * the content captureTo in layout
     * 
     * @param string $captureTo
     */
    public function __construct(Debugger $debugger, $captureTo = null)
    {
        $this->debugger = $debugger;
        if (null !== $captureTo) {
            $this->captureTo = $captureTo;
        }       
    }
    
    /**
     * 
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'prepareActionResultViewModel'));
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'addContentViewModels'), -4000);
    }  
    
    /**
     * remove/prepare view model from action/mvc event, so we later can inject
     * configured content view models in correct order
     * 
     * @param EventInterface $event
     * @return ContentViewModelsListener
     */
    public function prepareActionResultViewModel(EventInterface $event)
    {
        /* @var $layout ViewModel */
        $layout = $event->getViewModel();
        $this->actionResultViewModel = current($layout->getChildrenByCaptureTo($this->captureTo, false));
        if (false === $this->actionResultViewModel) {
            return $this;
        }
        if ($this->debugger->isEnabled()) {
            $this->actionResultViewModel->setVariable('nameInLayout', 'ACTION_RESULT');
            $this->actionResultViewModel = $this->debugger->addDebugBlock($this->actionResultViewModel, $this->captureTo);
        }
        $layout->clearChildren();
        return $this;
    }
    
    /**
     * sort content view models and add them to layout
     * 
     * @param EventInterface $event
     * @return ContentViewModelsListener
     */
    public function addContentViewModels(EventInterface $event)
    {
        if (!$this->actionResultViewModel instanceof ViewModel) {
            return $this;
        }
        /* @var $layout ViewModel */
        $layout = $event->getViewModel();
        $contentViewModels = $layout->getChildrenByCaptureTo($this->captureTo, false);
        $contentViewModels[] = $this->actionResultViewModel;
        
        // sort the view models
        usort($contentViewModels, function($a, $b) {
            /* @var $a ViewModel */
            /* @var $b ViewModel */
            $orderA = $a->getOption('order', 0);
            $orderB = $b->getOption('order', 0);            
            if ($orderA == $orderB) {
                return 0;
            }
            return ($orderA < $orderB) ? -1 : 1;
        });
        
        foreach ($contentViewModels as $i => $contentViewModel) {
            $layout->addChild($contentViewModel, $this->captureTo, ($i !== 0));
        }
        
        return $this;
    }
}
