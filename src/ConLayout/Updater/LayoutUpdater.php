<?php

namespace ConLayout\Updater;

use ConLayout\Handle\Handle;
use ConLayout\Handle\HandleInterface;
use Zend\Config\Config;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
final class LayoutUpdater implements
    EventManagerAwareInterface,
    LayoutUpdaterInterface
{
    use EventManagerAwareTrait;

    /**
     * Format:
     * (string) handle-name => (int) priority
     *
     * @var array
     */
    private $handles = [
        'default' => -1
    ];

    /**
     *
     * @var Config
     */
    private $globalLayoutStructure;

    /**
     *
     * @var Config
     */
    private $layoutStructure;

    /**
     * {@inheritdoc}
     */
    public function addHandle(HandleInterface $handle)
    {
        $this->handles[$handle->getName()] = $handle->getPriority();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeHandle($handleName)
    {
        if (isset($this->handles[$handleName])) {
            unset($this->handles[$handleName]);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHandles($asObject = false)
    {
        asort($this->handles);
        if ($asObject) {
            $handles = [];
            foreach ($this->handles as $handle => $priority) {
                $handles[] = new Handle($handle, $priority);
            }
            return $handles;
        }
        return array_keys($this->handles);
    }

    /**
     * {@inheritdoc}
     */
    public function getLayoutStructure($force = false)
    {
        if (null === $this->layoutStructure || $force) {
            $this->layoutStructure = new Config([], true);
            $this->loadGlobalLayoutStructure();
            foreach ($this->getHandles() as $handle) {
                $this->fetch($handle);
            }
        }
        return $this->layoutStructure;
    }

    /**
     * fetch layout structure for handle and merge with layout structure
     *
     * @param string $handleToFetch
     */
    private function fetch($handleToFetch)
    {
        $this->fetchApplyFor($handleToFetch);
        $this->fetchHandle($handleToFetch);
    }

    /**
     * fetches apply_for key:
     * 'my-handle' => [
     *     'apply_for' => [
     *         'another-handle' => true
     *     ]
     * ]
     *
     * @param string $handleToFetch
     */
    private function fetchApplyFor($handleToFetch)
    {
        foreach ($this->globalLayoutStructure as $instructions) {
            if ($includeHandles = $instructions->get(self::APPLY_FOR)) {
                if ($includeHandles->get($handleToFetch)) {
                    $this->layoutStructure->merge($instructions);
                }
            }
        }
    }

    /**
     * fetches handle
     *
     * @param string $handleToFetch
     */
    private function fetchHandle($handleToFetch)
    {
        if ($config = $this->globalLayoutStructure->get($handleToFetch)) {
            $this->layoutStructure->merge($config);
        }
    }

    /**
     * event driven load application wide layout structure
     */
    private function loadGlobalLayoutStructure()
    {
        $this->globalLayoutStructure = new Config([], true);
        $this->getEventManager()->trigger(
            __FUNCTION__, 
            $this,
            ['global_layout_structure' => $this->globalLayoutStructure]
        );
    }
}
