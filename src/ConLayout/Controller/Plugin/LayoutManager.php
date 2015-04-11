<?php
namespace ConLayout\Controller\Plugin;

use ConLayout\Handle\Handle;
use ConLayout\Handle\HandleInterface;
use ConLayout\LayoutInterface;
use ConLayout\LayoutManagerInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
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
     * @var LayoutInterface
     */
    protected $layout;

    /**
     *
     * @var LayoutUpdaterInterface
     */
    protected $updater;
    
    /**
     *
     * @var RendererInterface
     */
    protected $renderer;
        
    /**
     * 
     * @param LayoutInterface $layout
     * @param RendererInterface $renderer
     */
    public function __construct(
        LayoutInterface $layout,
        LayoutUpdaterInterface $updater,
        RendererInterface $renderer
    )
    {
        $this->layout = $layout;
        $this->updater = $updater;
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
            $this->layout->getBlock($blockIdOrViewModel)
        );
    }
    
    /**
     * 
     * @param string $blockId
     * @return ModelInterface
     */
    public function getBlock($blockId)
    {
        return $this->layout->getBlock($blockId);
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
        $this->updater->addHandle($handle);
        return $this;
    }
        
    /**
     * 
     * @param string $handle
     * @return LayoutManager
     */
    public function removeHandle($handle)
    {
        $this->updater->removeHandle($handle);
        return $this;
    }
}
