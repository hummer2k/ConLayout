<?php

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Generator;

use ConLayout\Exception\BadMethodCallException;
use ConLayout\Filter\FilterInterface;
use ConLayout\Filter\RawValueAwareInterface;
use ConLayout\NamedParametersTrait;
use ConLayout\Sorter\BeforeAfterComparison;
use ConLayout\Sorter\SorterInterface;
use Interop\Container\ContainerInterface;
use Laminas\Config\Config;
use Laminas\Stdlib\ArrayUtils;

final class ViewHelperGenerator implements GeneratorInterface
{
    use NamedParametersTrait;

    public const NAME          = 'helpers';
    public const INSTRUCTION   = 'helpers';

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @var ContainerInterface
     */
    private $viewHelperManager;

    /**
     * @var ContainerInterface
     */
    private $filterManager;

    /**
     * @var array
     */
    private $helperConfig = [];

    /**
     * @var SorterInterface
     */
    private $sorter;

    /**
     * ViewHelperGenerator constructor.
     * @param ContainerInterface $viewHelperManager
     * @param array $helperConfig
     */
    public function __construct(
        ContainerInterface $viewHelperManager,
        array $helperConfig = []
    ) {
        $this->viewHelperManager = $viewHelperManager;
        $this->helperConfig = $helperConfig;
    }

    /**
     * @return ContainerInterface
     */
    public function getFilterManager()
    {
        return $this->filterManager;
    }

    /**
     * @param ContainerInterface $filterManager
     * @return ViewHelperGenerator
     */
    public function setFilterManager(ContainerInterface $filterManager)
    {
        $this->filterManager = $filterManager;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function generate(Config $layoutStructure)
    {
        if ($helpers = $layoutStructure->get(self::INSTRUCTION)) {
            $this->generateViewHelpers($helpers);
        }
    }

    /**
     * @param Config $helpers
     * @throws BadMethodCallException
     */
    private function generateViewHelpers(Config $helpers)
    {
        foreach ($this->helperConfig as $helper => $config) {
            if (!$helpers->get($helper)) {
                continue;
            }
            $helperProxy = false;
            if (isset($config['proxy']) && $this->viewHelperManager->has($config['proxy'])) {
                $helperProxy = $this->viewHelperManager->get($config['proxy']);
            }
            $viewHelper = $this->viewHelperManager->get($helper);
            $instructions = $helpers[$helper]->toArray();
            $sortedInstructions = $this->sort($instructions);
            foreach ($sortedInstructions as $id => $instruction) {
                if ($this->isRemoved($instruction)) {
                    continue;
                }
                $mergedInstruction = ArrayUtils::merge($config, (array) $instruction);
                if ($this->isDebug() && isset($mergedInstruction['debug'])) {
                    $mergedInstruction[$mergedInstruction['debug']]['data-layout-id'] = $id;
                }

                $method = isset($mergedInstruction['method']) ? $mergedInstruction['method'] : '__invoke';
                $args = $this->filterArgs($mergedInstruction);

                if (method_exists($viewHelper, $method)) {
                    $this->invokeArgs($viewHelper, $method, $args);
                } elseif (false !== $helperProxy && method_exists($helperProxy, $method)) {
                    $this->invokeArgs($helperProxy, $method, $args);
                } else {
                    throw new BadMethodCallException(sprintf(
                        'Call to undefined helper method %s::%s()',
                        get_class($viewHelper),
                        $method
                    ));
                }
            }
        }
    }

    /**
     *
     * @param array $data
     * @return array
     */
    private function sort($data)
    {
        return $this->getSorter()->sort($data);
    }

    /**
     * @param array $instruction
     * @return array
     */
    private function filterArgs(array $instruction)
    {
        if (
            !isset($instruction['filter']) ||
            !$instruction['filter'] ||
            null === $this->filterManager
        ) {
            return $instruction;
        }
        foreach ((array) $instruction['filter'] as $param => $filters) {
            if (!$filters || !isset($instruction[$param])) {
                continue;
            }
            $rawValue = $instruction[$param];
            /* @var $filter FilterInterface */
            asort($filters);
            foreach ($filters as $filterName => $filterSpecs) {
                $isEnabled = filter_var($filterSpecs, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if (false === $isEnabled || !$this->filterManager->has($filterName)) {
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

    private function isRemoved($item)
    {
        if (!$item) {
            return true;
        }
        if (!isset($item['remove'])) {
            return false;
        }
        return filter_var($item['remove'], FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return SorterInterface
     */
    public function getSorter()
    {
        if (!$this->sorter) {
            $this->sorter = new BeforeAfterComparison();
        }
        return $this->sorter;
    }

    /**
     * @param SorterInterface $sorter
     * @return ViewHelperGenerator
     */
    public function setSorter(SorterInterface $sorter)
    {
        $this->sorter = $sorter;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * @param boolean $debug
     * @return ViewHelperGenerator
     */
    public function setDebug($debug)
    {
        $this->debug = (bool) $debug;
        return $this;
    }
}
