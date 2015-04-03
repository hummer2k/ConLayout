<?php
namespace ConLayout\Controller\Plugin;

use ConLayout\Service\BlocksBuilder,
    ConLayout\Service\LayoutService,
    Zend\Mvc\Controller\Plugin\AbstractPlugin,
    Zend\ServiceManager\ServiceLocatorAwareInterface,
    Zend\ServiceManager\ServiceLocatorAwareTrait,
    Zend\View\Model\ModelInterface,
    Zend\View\Model\ViewModel,
    Zend\View\Renderer\RendererInterface;
    
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutManager
    extends AbstractPlugin
    implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    
    /**
     *
     * @var BlocksBuilder
     */
    protected $blocksBuilder;
    
    /**
     *
     * @var RendererInterface
     */
    protected $renderer;
    
    /**
     *
     * @var LayoutService
     */
    protected $layoutService;
    
    /**
     * 
     * @param BlocksBuilder $blocksBuilder
     * @param LayoutService $layoutService
     * @param RendererInterface $renderer
     */
    public function __construct(
        BlocksBuilder $blocksBuilder,
        LayoutService $layoutService,
        RendererInterface $renderer
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
        if ($blockname instanceof ModelInterface) {
            return $this->renderer->render($blockname);
        }
        return $this->renderer->render(
            $this->blocksBuilder->getBlock($blockname)
        );
    }
    
    /**
     * 
     * @param string $blockname
     * @return ViewModel
     */
    public function getBlock($blockname)
    {
        $blockConfig = $this->layoutService->getBlockConfig();
        $this->blocksBuilder->setBlockConfig($blockConfig);
        return $this->blocksBuilder->getBlock($blockname);
    }
    
    /**
     * 
     * @return LayoutService
     */
    public function getLayoutService()
    {
        return $this->layoutService;
    }
    
    /**
     * 
     * @param string $handle
     * @return \ConLayout\Controller\Plugin\LayoutManager
     */
    public function addHandle($handle)
    {
        $this->layoutService->addHandle($handle);
        return $this;
    }
    
    /**
     * 
     * @param array|string $handles
     * @return \ConLayout\Controller\Plugin\LayoutManager
     */
    public function setHandles($handles)
    {
        $this->layoutService->setHandles($handles);
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
