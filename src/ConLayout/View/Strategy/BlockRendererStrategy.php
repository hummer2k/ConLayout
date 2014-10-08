<?php
namespace ConLayout\View\Strategy;

use Zend\EventManager\AbstractListenerAggregate,
    ConLayout\View\Renderer\BlockRenderer,
    Zend\View\ViewEvent,
    ConLayout\Block\BlockInterface,
    Zend\EventManager\EventManagerInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockRendererStrategy
    extends AbstractListenerAggregate    
{
    /**
     *
     * @var BlockRenderer
     */
    protected $renderer;
    
    /**
     * 
     * @param BlockRenderer $renderer
     */
    public function __construct(BlockRenderer $renderer)
    {
        $this->renderer = $renderer;
    }
    
    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 5)
    {
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RENDERER, array($this, 'selectRenderer'), $priority);
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RESPONSE, array($this, 'injectResponse'), $priority);
    }
    
    /**
     * 
     * @param \Zend\View\ViewEvent $e
     * @return RendererInterface|null
     */
    public function selectRenderer(ViewEvent $e)
    {
        $model = $e->getModel();
        if (!$model instanceof BlockInterface) {
            return;
        }
        return $this->renderer;
    }
    
    /**
     * 
     * @param ViewEvent $e
     * @return null
     */
    public function injectResponse(ViewEvent $e)
    {
        $renderer = $e->getRenderer();
        if ($renderer !== $this->renderer) {
            return;
        }
        $result   = $e->getResult();
        $response = $e->getResponse();
        $response->setContent($result);
    }
}
