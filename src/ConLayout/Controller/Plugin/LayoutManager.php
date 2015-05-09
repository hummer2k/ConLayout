<?php
namespace ConLayout\Controller\Plugin;

use ConLayout\Handle\Handle;
use ConLayout\Handle\HandleInterface;
use ConLayout\Layout\LayoutInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Model\ModelInterface;
use Zend\View\Renderer\RendererInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutManager extends AbstractPlugin implements
    LayoutInterface
{
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
    ) {
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
    public function addBlock($blockId, ModelInterface $block)
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
     * @param array $handles
     * @return LayoutManager
     */
    public function setHandles(array $handles)
    {
        $newHandles = [];
        foreach ($handles as $handle => $priority) {
            if (is_string($handle) && !$priority instanceof HandleInterface) {
                $handle = new Handle($handle, $priority);
            } elseif ($priority instanceof HandleInterface) {
                $handle = $priority;
            }
            $newHandles[] = $handle;
        }
        $this->updater->setHandles($handles);
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

    /**
     *
     * @return ModelInterface[]
     */
    public function getBlocks()
    {
        return $this->layout->getBlocks();
    }

    /**
     *
     * @return LayoutManager
     */
    public function load()
    {
        $this->layout->load();
        return $this;
    }

    /**
     *
     * @param ModelInterface $root
     * @return LayoutManager
     */
    public function setRoot(ModelInterface $root)
    {
        $this->layout->setRoot($root);
        return $this;
    }
}
