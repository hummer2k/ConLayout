<?php
namespace ConLayout\View\Strategy;

use ConLayout\Block\BlockInterface;
use ConLayout\View\Renderer\BlockRenderer;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Renderer\RendererInterface;
use Zend\View\ViewEvent;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockRendererStrategy extends AbstractListenerAggregate
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
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RENDERER, [$this, 'selectRenderer'], $priority);
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RESPONSE, [$this, 'injectResponse'], $priority);
    }

    /**
     *
     * @param ViewEvent $e
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
