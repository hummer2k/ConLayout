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

    /**
     * find helper method
     *
     * @param array $value
     * @param string $defaultMethod
     * @param AbstractHelper $viewHelper
     * @return string
     */
    private function getHelperMethod(array $value, $defaultMethod, $viewHelper)
    {
        if (isset($value['method']) && is_callable([$viewHelper, $value['method']])) {
            return $value['method'];
        } else {
            // @codeCoverageIgnoreStart
            $method = current(array_keys($value));
            if (is_string($method) && is_callable([$viewHelper, $method])) {
                $val = current($value);
                trigger_error(sprintf(
                    '%s::%s Calling method via key in layout instruction is deprecated.'
                    . ' ["%s" => "%s"] should be ["id" => ["method" => "%s", "args" => ["%s"]]',
                    get_class($viewHelper),
                    $method,
                    $method,
                    $val,
                    $method,
                    $val
                ), E_USER_DEPRECATED);
                return $method;
            }
            // @codeCoverageIgnoreEnd
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
