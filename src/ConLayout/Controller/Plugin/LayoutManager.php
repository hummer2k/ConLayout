<?php
namespace ConLayout\Controller\Plugin;

use ConLayout\Generator\GeneratorInterface;
use ConLayout\Handle\Handle;
use ConLayout\Handle\HandleInterface;
use ConLayout\Layout\LayoutInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Model\ModelInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutManager extends AbstractPlugin implements
    LayoutInterface,
    LayoutUpdaterInterface
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
     * @param LayoutInterface $layout
     * @param LayoutUpdaterInterface $updater
     */
    public function __construct(
        LayoutInterface $layout,
        LayoutUpdaterInterface $updater
    ) {
        $this->layout = $layout;
        $this->updater = $updater;
    }

    /**
     *
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
     * @inheritDoc
     */
    public function getLayoutStructure()
    {
        return $this->updater->getLayoutStructure();
    }

    /**
     * @inheritDoc
     */
    public function addHandle(HandleInterface $handle)
    {
        $this->updater->addHandle($handle);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setArea($area)
    {
        $this->updater->setArea($area);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getArea()
    {
        return $this->updater->getArea();
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
        $this->updater->setHandles($newHandles);
        return $this;
    }

    public function getHandles($asObject = false)
    {
        return $this->updater->getHandles($asObject);
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
     * {@inheritdoc}
     */
    public function getRoot()
    {
        return $this->layout->getRoot();
    }

    /**
     * @inheritDoc
     */
    public function setRoot(ModelInterface $root)
    {
        $this->layout->setRoot($root);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function attachGenerator($name, GeneratorInterface $generator, $priority = 1)
    {
        $this->layout->attachGenerator($name, $generator, $priority);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function detachGenerator($name)
    {
        $this->layout->detachGenerator($name);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function generate(array $generators = [])
    {
        $this->layout->generate($generators);
        return $this;
    }
}
