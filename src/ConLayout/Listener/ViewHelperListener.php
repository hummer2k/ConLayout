<?php

namespace ConLayout\Listener;

use ConLayout\AssetPreparer\AssetPreparerInterface;
use ConLayout\AssetPreparer\OriginalValueAwareInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\Config\Config;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;
use Zend\View\HelperPluginManager;

/**
 * Listener to apply view helpers from layout structure
 *
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ViewHelperListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     *
     * @var LayoutUpdaterInterface
     */
    protected $updater;

    /**
     *
     * @var HelperPluginManager
     */
    protected $viewHelperManager;

    /**
     *
     * @var array
     */
    protected $helperConfig = [];

    /**
     * asset preparers for view helpers in format:
     * 'helperName' => [
     *     AssetPreparerInterface,
     *     AssetPreparerInterface
     * ]
     *
     * @var AssetPreparerInterface[]
     */
    protected $assetPreparers = [];

    /**
     *
     * @param LayoutUpdaterInterface $updater
     * @param HelperPluginManager $viewHelperManager
     * @param array $helperConfig
     */
    public function __construct(
        LayoutUpdaterInterface $updater,
        HelperPluginManager $viewHelperManager,
        array $helperConfig
    ) {
        $this->updater           = $updater;
        $this->viewHelperManager = $viewHelperManager;
        $this->helperConfig      = $helperConfig;
    }

    /**
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->getSharedManager()
            ->attach(
                'ConLayout\Layout\Layout',
                'load.pre',
                [$this, 'applyViewHelpers']
            );
    }

    /**
     * applies view helpers
     *
     * @todo refactor
     * @param MvcEvent $e
     * @return LayoutModifierListener
     */
    public function applyViewHelpers(EventInterface $e)
    {
        $viewHelperInstructions = $this->updater->getLayoutStructure()->get(
            LayoutUpdaterInterface::INSTRUCTION_VIEW_HELPERS
        );
        if ($viewHelperInstructions instanceof Config) {
            $viewHelperInstructions = $viewHelperInstructions->toArray();
        }
        foreach ($this->helperConfig as $helper => $config) {
            if (!isset($viewHelperInstructions[$helper])) {
                continue;
            }
            $defaultMethod = isset($config['default_method']) ? $config['default_method'] : '__invoke';
            $viewHelper = $this->viewHelperManager->get($helper);
            $viewHelperInstructions[$helper] = (array) $viewHelperInstructions[$helper];
            foreach ($viewHelperInstructions[$helper] as $value) {
                if (!$value) {
                    continue;
                }
                $value = (array) $value;
                $method = $this->getHelperMethod($value, $defaultMethod, $viewHelper);
                $args   = isset($value['args']) ? (array) $value['args'] : array_values($value);
                $args[0] = $this->prepareHelperValue($args[0], $helper);
                call_user_func_array([$viewHelper, $method], $args);
            }
        }
        return $this;
    }

    private function getHelperMethod($value, $defaultMethod, $viewHelper)
    {
        if (isset($value['method']) && is_callable([$viewHelper, $value['method']])) {
            return $value['method'];
        }
        return $defaultMethod;
    }

    /**
     *
     * @param mixed $value value to prepare
     * @param string $helper view helper name
     * @return mixed
     */
    protected function prepareHelperValue($value, $helper)
    {
        if (!isset($this->assetPreparers[$helper])) {
            return $value;
        }
        $originalValue = $value;
        /* @var $assetPreparer AssetPreparerInterface */
        foreach ($this->assetPreparers[$helper] as $assetPreparer) {
            if ($assetPreparer instanceof OriginalValueAwareInterface) {
                $assetPreparer->setOriginalValue($originalValue);
            }
            $value = $assetPreparer->prepare($value);
        }
        return $value;
    }

    /**
     *
     * @param string $helper
     * @param AssetPreparerInterface $assetPreparer
     */
    public function addAssetPreparer($helper, AssetPreparerInterface $assetPreparer)
    {
        if (!isset($this->assetPreparers[$helper])) {
            $this->assetPreparers[$helper] = [];
        }
        $this->assetPreparers[$helper][] = $assetPreparer;
    }
}
