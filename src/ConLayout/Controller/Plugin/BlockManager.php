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
    
    protected $blocksBuilder;
    
    protected $renderer;
    
    protected $config;
    
    /**
     * 
     * @param \ConLayout\Service\BlocksBuilder $blocksBuilder
     */
    public function __construct(
        \ConLayout\Service\BlocksBuilder $blocksBuilder,
        \ConLayout\Service\Config $config,
        \Zend\View\Renderer\RendererInterface $renderer
    )
    {
        $this->blocksBuilder = $blocksBuilder;
        $this->config = $config;
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
     * @param string $blockname
     * @return string
     */
    public function render($blockname)
    {
        return $this->renderer->render(
            $this->blocksBuilder->getBlock($blockname)
        );
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
     * @return 
     */
    public function getConfig()
    {
        return $this->config;
    }
    
    /**
     * 
     * @param string $handle
     * @return \ConLayout\Controller\Plugin\Blocks
     */
    public function addHandle($handle)
    {
        $this->config->addHandle($handle);
        return $this;
    }
    
    /**
     * 
     * @param array|string $handles
     * @return \ConLayout\Controller\Plugin\Blocks
     */
    public function removeHandle($handles)
    {
        $this->config->removeHandle($handles);
        return $this;
    }
}
