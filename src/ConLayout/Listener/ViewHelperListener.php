<?php

namespace ConLayout\Listener;

use ConLayout\Exception\BadMethodCallException;
use ConLayout\Filter\RawValueAwareInterface;
use ConLayout\NamedParametersTrait;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\Config\Config;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Filter\FilterInterface;
use Zend\Filter\FilterPluginManager;
use Zend\Stdlib\ArrayUtils;
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
     * @var FilterPluginManager
     */
    private $filterManager;

    /**
     *
     * @var array
     */
    protected $helperConfig = [];

    /**
     *
     * @param LayoutUpdaterInterface $updater
     * @param HelperPluginManager $viewHelperManager
     * @param FilterPluginManager $filterManager
     * @param array $helperConfig
     */
    public function __construct(
        LayoutUpdaterInterface $updater,
        HelperPluginManager $viewHelperManager,
        FilterPluginManager $filterManager,
        array $helperConfig
    ) {
        $this->updater           = $updater;
        $this->viewHelperManager = $viewHelperManager;
        $this->filterManager     = $filterManager;
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
        foreach ($this->helperConfig as $helper => $config) {
            if (!isset($viewHelperInstructions[$helper])) {
                continue;
            }
            $helperProxy = false;
            if (isset($config['proxy']) && $this->viewHelperManager->has($config['proxy'])) {
                $helperProxy = $this->viewHelperManager->get($config['proxy']);
            }
            $viewHelper = $this->viewHelperManager->get($helper);
            $sortedInstructions = $this->sort(
                (array) $viewHelperInstructions[$helper]
            );
            foreach ($sortedInstructions as $instruction) {
                if (!$instruction) {
                    continue;
                }
                if (is_string($instruction) && isset($config['default_param'])) {
                    $instruction = [$config['default_param'] => $instruction];
                }
                $mergedInstruction = [];
                $mergedInstruction = ArrayUtils::merge($config, (array) $instruction);
                $method = isset($mergedInstruction['method']) ? $mergedInstruction['method'] : '__invoke';
                $args = $this->filterArgs($mergedInstruction);

                if (method_exists($viewHelper, $method)) {
                    $this->invokeArgs($viewHelper, $method, $args);
                } elseif (false !== $helperProxy && method_exists($helperProxy, $method)) {
                    $this->invokeArgs($helperProxy, $method, $args);
                } elseif (is_callable([$viewHelper, $method])) {
                    call_user_func_array([$viewHelper, $method], array_values($args));
                } else {
                    throw new BadMethodCallException(sprintf(
                        'Call to undefined helper method %s::%s() in %s on line %d',
                        get_class($viewHelper),
                        $method,
                        __FILE__,
                        __LINE__
                    ));
                }
            }
        }
        return $this;
    }

    /**
     *
     * @param array $array
     * @return array
     */
    protected function sort(array $array)
    {
        $tmp = [];
        foreach (array_keys($array) as $key) {
            $tmp[] = $this->getNodeLevel($array, $key);
        }
        array_multisort($tmp, SORT_ASC, $array);
        return $array;
    }

    /**
     *
     * @param array $array
     * @param string $key
     * @param array $references
     * @return int
     */
    private function getNodeLevel(array $array, $key, array $references = [])
    {
        if (!isset($array[$key]['depends'])) {
            return 0;
        }
        if (in_array($key, $references)) {
            return -1;
        }
        $references[] = $key;
        $level = $this->getNodeLevel($array, $array[$key]['depends'], $references);
        return ($level == -1 ? -1 : $level + 1);
    }

    /**
     * @param array $instruction
     * @return array
     */
    private function filterArgs(array $instruction)
    {
        if (!isset($instruction['filter']) || !$instruction['filter']) {
            return $instruction;
        }
        foreach ((array) $instruction['filter'] as $param => $filters) {
            if (!$filters || !isset($instruction[$param])) {
                continue;
            }
            $rawValue = $instruction[$param];
            /* @var $filter FilterInterface */
            asort($filters);
            foreach ($filters as $filterName => $isEnabled) {
                if (floatval($isEnabled) <= 0 || !$this->filterManager->has($filterName)) {
                    continue;
                }
                $filter = $this->filterManager->get($filterName);
                if ($filter instanceof RawValueAwareInterface) {
                    $filter->setRawValue($rawValue);
                }
                $instruction[$param] = $filter->filter($instruction[$param]);
            }
        }
        return $instruction;
    }
}
