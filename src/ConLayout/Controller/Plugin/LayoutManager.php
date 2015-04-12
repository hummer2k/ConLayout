<?php
namespace ConLayout\Controller\Plugin;

use ConLayout\Handle\Handle;
use ConLayout\Handle\HandleInterface;
use ConLayout\Layout\LayoutInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Exception\DomainException;
use Zend\View\Model\ModelInterface;
use Zend\View\Renderer\RendererInterface;
use Zend\View\Renderer\TreeRendererInterface;
    
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutManager
    extends AbstractPlugin
    implements 
        ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * flag to check if blocks are already injected
     *
     * @var bool
     */
    protected $blocksInjected = false;
    
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
     * @param LayoutUpdaterInterface $updater
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
        if (!$this->blocksInjected) {
            $this->layout->injectBlocks();
            $this->blocksInjected = true;
        }
        if (is_string($blockIdOrViewModel)) {
            $blockIdOrViewModel = $this->layout->getBlock($blockIdOrViewModel);
        }
        if (!$blockIdOrViewModel instanceof ModelInterface) {
            return '';
        }

        if ($blockIdOrViewModel->hasChildren()
            && (!$this->renderer instanceof TreeRendererInterface
                || !$this->renderer->canRenderTrees())
        ) {
            $this->renderChildren($blockIdOrViewModel);
        }

        return $this->renderer->render($blockIdOrViewModel);
    }

    /**
     * Loop through children, rendering each
     *
     * @param  ModelInterface $viewModel
     * @throws DomainException
     * @return void
     */
    protected function renderChildren(ModelInterface $viewModel)
    {
        foreach ($viewModel as $child) {
            if ($child->terminate()) {
                throw new DomainException(
                    'Inconsistent state; child view model is marked as terminal'
                );
            }
            $result = $this->render($child);
            $capture = $child->captureTo();
            if (!empty($capture)) {
                if ($child->isAppend()) {
                    $oldResult = $viewModel->{$capture};
                    $viewModel->setVariable($capture, $oldResult . $result);
                } else {
                    $viewModel->setVariable($capture, $result);
                }
            }
        }
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
     * @param string $blockId
     * @param ModelInterface $block
     * @return LayoutManager
     */
    public function addBlock($blockId, $block)
    {
        $this->layout->addBlock($blockId, $block);
        return $this;
    }

    /**
     *
     * @param string $blockId
     * @return LayoutManager
     */
    public function removeBlock($blockId)
    {
        $this->layout->removeBlock($blockId);
        return $this;
    }

    /**
     * add a handle. if $handle parameter implements HandleInterface, $priority
     * will be ignored
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
