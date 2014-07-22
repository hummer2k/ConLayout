<?php
namespace ConLayout\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin,
    Zend\ServiceManager\ServiceLocatorAwareTrait,
    Zend\ServiceManager\ServiceLocatorAwareInterface;
    
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockManager
    extends AbstractPlugin
    implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    
    /**
     *
     * @var \ConLayout\Service\BlocksBuilder
     */
    protected $blocksBuilder;
    
    /**
     *
     * @var \Zend\View\Renderer\RendererInterface
     */
    protected $renderer;
    
    /**
     *
     * @var \ConLayout\Service\LayoutService
     */
    protected $layoutService;
    
    /**
     * 
     * @param \ConLayout\Service\BlocksBuilder $blocksBuilder
     * @param \ConLayout\Service\LayoutService $layoutService
     * @param \Zend\View\Renderer\RendererInterface $renderer
     */
    public function __construct(
        \ConLayout\Service\BlocksBuilder $blocksBuilder,
        \ConLayout\Service\LayoutService $layoutService,
        \Zend\View\Renderer\RendererInterface $renderer
    )
    {
        $this->blocksBuilder = $blocksBuilder;
        $this->layoutService = $layoutService;
        $this->renderer = $renderer;
    }
    
    /**
     * 
     * @param string|null $blockname
     * @return mixed
     */
    public function __invoke($blockname = null)
    {
        if (null === $blockname) {
            return $this;
        }
        return $this->blocksBuilder->getBlock($blockname);
    }
    
    /**
     * 
     * @param mixed $blockname 
     * @return string rendered block
     */
    public function render($blockname)
    {
        if ($blockname instanceof \Zend\View\Model\ModelInterface) {
            return $this->renderer->render($blockname);
        }
        return $this->renderer->render(
            $this->blocksBuilder->getBlock($blockname)
        );
    }
    
    /**
     * 
     * @param string $blockname
     * @return \Zend\View\Model\ViewModel
     */
    public function getBlock($blockname)
    {
        return $this->blocksBuilder->getBlock($blockname);
    }
    
    /**
     * 
     * @return array
     */
    public function getBlocks()
    {
        return $this->blocksBuilder->getBlocks();
    }
    
    /**
     * 
     * @return \ConLayout\Service\LayoutService
     */
    public function getLayoutService()
    {
        return $this->layoutService;
    }
    
    /**
     * 
     * @param string $handle
     * @return \ConLayout\Controller\Plugin\Blocks
     */
    public function addHandle($handle)
    {
        $this->layoutService->addHandle($handle);
        return $this;
    }
    
    /**
     * 
     * @param array|string $handles
     * @return \ConLayout\Controller\Plugin\Blocks
     */
    public function removeHandle($handles)
    {
        $this->layoutService->removeHandle($handles);
        return $this;
    }
}
