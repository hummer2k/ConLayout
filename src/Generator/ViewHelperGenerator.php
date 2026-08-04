<?php

declare(strict_types=1);

namespace ConLayout\Generator;

use ConLayout\Exception\BadMethodCallException;
use ConLayout\Filter\FilterInterface;
use ConLayout\Filter\RawValueAwareInterface;
use ConLayout\NamedParametersTrait;
use ConLayout\Sorter\BeforeAfterComparison;
use ConLayout\Sorter\SorterInterface;
use Psr\Container\ContainerInterface;
use Laminas\Config\Config;
use Laminas\Stdlib\ArrayUtils;

final class ViewHelperGenerator implements GeneratorInterface
{
    use NamedParametersTrait;

    public const NAME          = 'helpers';
    public const INSTRUCTION   = 'helpers';

    private bool $debug = false;

    private ContainerInterface $viewHelperManager;

    private ?ContainerInterface $filterManager = null;

    private array $helperConfig = [];

    private ?SorterInterface $sorter = null;

    /**
     * ViewHelperGenerator constructor.
     */
    public function __construct(
        ContainerInterface $viewHelperManager,
        array $helperConfig = [],
    ) {
        $this->viewHelperManager = $viewHelperManager;
        $this->helperConfig = $helperConfig;
    }

    public function getFilterManager(): ?ContainerInterface
    {
        return $this->filterManager;
    }

    public function setFilterManager(ContainerInterface $filterManager): static
    {
        $this->filterManager = $filterManager;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function generate(Config $layoutStructure): void
    {
        if ($helpers = $layoutStructure->get(self::INSTRUCTION)) {
            $this->generateViewHelpers($helpers);
        }
    }

    /**
     * @throws BadMethodCallException
     */
    private function generateViewHelpers(Config $helpers): void
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
                        $method,
                    ));
                }
            }
        }
    }

    private function sort(array $data): array
    {
        return $this->getSorter()->sort($data);
    }

    private function filterArgs(array $instruction): array
    {
        if (
            !isset($instruction['filter'])
            || !$instruction['filter']
            || null === $this->filterManager
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

    private function isRemoved(array $item): bool
    {
        if (!$item) {
            return true;
        }
        if (!isset($item['remove'])) {
            return false;
        }
        return (bool) filter_var($item['remove'], FILTER_VALIDATE_BOOLEAN);
    }

    public function getSorter(): SorterInterface
    {
        if (!$this->sorter) {
            $this->sorter = new BeforeAfterComparison();
        }
        return $this->sorter;
    }

    public function setSorter(SorterInterface $sorter): static
    {
        $this->sorter = $sorter;
        return $this;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function setDebug(bool $debug): static
    {
        $this->debug = $debug;
        return $this;
    }
}
