<?php
namespace ConLayout\Controller\Plugin;

use ConLayout\Handle\Handle;
use ConLayout\Handle\HandleInterface;
use ConLayout\LayoutManagerInterface;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Model\ModelInterface;
use Zend\View\Renderer\RendererInterface;
    
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutManager
    extends AbstractPlugin
    implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    
    /**
     *
     * @var LayoutManagerInterface
     */
    protected $layoutManager;
    
    /**
     *
     * @var RendererInterface
     */
    protected $renderer;
        
    /**
     * 
     * @param LayoutManagerInterface $layoutManager
     * @param RendererInterface $renderer
     */
    public function __construct(
        LayoutManagerInterface $layoutManager,
        RendererInterface $renderer
    )
    {
        $this->layoutManager = $layoutManager;
        $this->renderer = $renderer;
    }
    
    /**
     * 
     * @param string|null $blockId
     * @return mixed
     */
    public function __invoke()
    {
        return $this;
    }
    
    /**
     * 
     * @param string|ModelInterface $blockIdOrViewModel
     * @return string rendered block
     */
    public function render($blockIdOrViewModel)
    {
        if ($blockIdOrViewModel instanceof ModelInterface) {
            return $this->renderer->render($blockIdOrViewModel);
        }
        return $this->renderer->render(
            $this->layoutManager->getBlock($blockIdOrViewModel)
        );
    }
    
    /**
     * 
     * @param string $blockId
     * @return ModelInterface
     */
    public function getBlock($blockId)
    {
        return $this->layoutManager->getBlock($blockId);
    }

    /**
     * 
     * @param string|HandleInterface $handle
     * @return LayoutManager
     */
    public function addHandle($handle, $priority = 1)
    {
        if (is_string($handle)) {
            $handle = new Handle($handle, $priority);
        }
        $this->layoutManager->addHandle($handle);
        return $this;
    }
        
    /**
     * 
     * @param array|string $handles
     * @return LayoutManager
     */
    public function removeHandle($handles)
    {
        $this->layoutManager->removeHandle($handles);
        return $this;
    }
}
