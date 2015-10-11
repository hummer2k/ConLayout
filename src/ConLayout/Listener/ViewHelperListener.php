<?php

namespace ConLayout\Listener;

use ConLayout\AssetPreparer\AssetPreparerInterface;
use ConLayout\AssetPreparer\OriginalValueAwareInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
use ConLayout\NamedParametersTrait;
use Zend\Config\Config;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\View\Helper\AbstractHelper;
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
    use NamedParametersTrait;

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
     * @return LayoutModifierListener
     */
    public function applyViewHelpers()
    {
        $viewHelperInstructions = $this->updater->getLayoutStructure()->get(
            LayoutUpdaterInterface::INSTRUCTION_VIEW_HELPERS
        );
        if ($viewHelperInstructions instanceof Config) {
            $viewHelperInstructions = $viewHelperInstructions->toArray();
        }
        $mainServiceLocator = $this->viewHelperManager->getServiceLocator();
        foreach ($this->helperConfig as $helper => $config) {
            if (!isset($viewHelperInstructions[$helper])) {
                continue;
            }
            $defaultMethod = isset($config['default_method']) ? $config['default_method'] : '__invoke';
            $proxyHelper = false;
            if (isset($config['proxy']) && $mainServiceLocator->has($config['proxy'])) {
                $proxyHelper = $mainServiceLocator->get($config['proxy']);
            }
            $viewHelper = $this->viewHelperManager->get($helper);
            $viewHelperInstructions[$helper] = (array) $viewHelperInstructions[$helper];
            foreach ($viewHelperInstructions[$helper] as $value) {
                if (!$value) {
                    continue;
                }
                if (is_string($value)) {
                    $method = $defaultMethod;
                    $defaultParamName = isset($config['default_param_name']) ? $config['default_param_name'] : 0;
                    $args = [$defaultParamName => $value];
                } else {
                    $method = isset($value['method']) ? $value['method'] : $defaultMethod;
                    $args   = isset($value['args']) ? $value['args'] : $value;
                }
                $args = $this->prepareArgs($helper, $args, $value, $config);
                if (method_exists($viewHelper, $method)) {
                    $this->invokeArgs($viewHelper, $method, $args);
                } elseif (false !== $proxyHelper && method_exists($proxyHelper, $method)) {
                    $this->invokeArgs($proxyHelper, $method, $args);
                } elseif (is_callable([$viewHelper, $method])) {
                    call_user_func_array([$viewHelper, $method], array_values($args));
                }
            }
        }
        return $this;
    }

    /**
     *
     * @param string $helper
     * @param array $args
     * @param mixed $value
     * @param array $config
     * @return array
     */
    private function prepareArgs($helper, array $args, $value, array $config)
    {
        if (!isset($this->assetPreparers[$helper])) {
            return $args;
        }
        foreach ($config['prepare_params'] as $param => $isEnabled) {
            if (!$isEnabled || !isset($args[$param])) {
                continue;
            }
            $originalValue = $args[$param];
            /* @var $assetPreparer AssetPreparerInterface */
            foreach ($this->assetPreparers[$helper] as $name => $assetPreparer) {
                if (isset($value['prepare'][$name]) && !$value['prepare'][$name]) {
                    continue;
                }
                if ($assetPreparer instanceof OriginalValueAwareInterface) {
                    $assetPreparer->setOriginalValue($originalValue);
                }
                $args[$param] = $assetPreparer->prepare($args[$param]);
            }
        }

        return $args;
    }

    /**
     *
     * @param string $helper
     * @param AssetPreparerInterface $assetPreparer
     */
    public function addAssetPreparer(
        $helper,
        AssetPreparerInterface $assetPreparer,
        $name = null
    ) {
        if (!isset($this->assetPreparers[$helper])) {
            $this->assetPreparers[$helper] = [];
        }
        $this->assetPreparers[$helper][$name] = $assetPreparer;
    }
}
