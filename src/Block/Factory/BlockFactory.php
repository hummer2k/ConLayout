<?php

namespace ConLayout\Block\Factory;

use ConLayout\Block\BlockInterface;
use ConLayout\Exception\BadMethodCallException;
use ConLayout\Exception\InvalidBlockException;
use ConLayout\NamedParametersTrait;
use Interop\Container\ContainerInterface;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\Stdlib\ArrayUtils;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Model\ViewModel;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
final class BlockFactory implements
    BlockFactoryInterface,
    EventManagerAwareInterface
{
    use EventManagerAwareTrait;
    use NamedParametersTrait;

    /**
     *
     * @var array
     */
    private $blockDefaults = [
        'capture_to' => 'content',
        'append'     => true,
        'class'      => ViewModel::class,
        'options'    => [],
        'variables'  => [],
        'template'   => '',
        'actions'    => [],
        'blocks'     => []
    ];

    /**
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ContainerInterface
     */
    private $blockManager;

    /**
     * BlockFactory constructor.
     * @param array $blockDefaults
     * @param ContainerInterface $blockManager
     * @param ContainerInterface $container
     */
    public function __construct(
        array $blockDefaults = [],
        ContainerInterface $blockManager = null,
        ContainerInterface $container = null
    ) {
        $this->blockDefaults = ArrayUtils::merge(
            $this->blockDefaults,
            $blockDefaults
        );
        $this->container = $container;
        $this->blockManager = $blockManager;
    }

    /**
     *
     * @param string $blockId
     * @param array $specs
     * @return ModelInterface
     */
    public function createBlock($blockId, array $specs)
    {
        /* @var $block ModelInterface */
        $class = $this->getOption('class', $specs);
        if ($this->blockManager->has($class)) {
            $block = $this->blockManager->get($class);
        } elseif (class_exists($class)) {
            $block = new $class();
        } else {
            throw new InvalidBlockException(sprintf(
                'Block "%s" could not be instantiated. Class does not exist.',
                $class
            ));
        }
        $block->setOption('block_id', $blockId);

        return $this->configure($block, $specs);
    }

    private function prepareOptions(array $specs)
    {
        foreach ($specs as $key => $value) {
            if (
                !isset($this->blockDefaults[$key])
                && !isset($specs['options'][$key])
            ) {
                $specs['options'][$key] = $value;
            }
        }
        return $specs;
    }

    /**
     * @inheritDoc
     */
    public function configure(ModelInterface $block, array $specs)
    {
        $specs = $this->prepareOptions($specs);

        foreach ($this->getOption('options', $specs) as $name => $option) {
            $block->setOption($name, $option);
        }
        foreach ($this->getOption('variables', $specs) as $name => $variable) {
            $block->setVariable($name, $variable);
        }
        foreach ($this->getOption('actions', $specs) as $params) {
            if (isset($params['method'])) {
                $method = (string) $params['method'];
                if (method_exists($block, $method)) {
                    $this->invokeArgs($block, $method, $params);
                } else {
                    throw new BadMethodCallException(sprintf(
                        'Call to undefined block method %s::%s()',
                        get_class($block),
                        $method
                    ));
                }
            }
        }
        if ($template = $this->getOption('template', $specs)) {
            $block->setTemplate($template);
        }
        $block->setCaptureTo($this->getOption('capture_to', $specs));
        $block->setAppend($this->getOption('append', $specs));
        $block->setVariable('block', $block);

        if ($block instanceof BlockInterface) {
            $block->setView($this->container->get('ViewRenderer'));
        }

        $results = $this->getEventManager()->trigger(
            __FUNCTION__ . '.post',
            $this,
            [
                'block' => $block,
                'specs' => $specs
            ],
            function ($result) {
                return $result instanceof ModelInterface;
            }
        );
        if ($results->stopped()) {
            $block = $results->last();
        }
        return $block;
    }

    /**
     *
     * @param string $name
     * @param array $specs
     * @return mixed
     */
    private function getOption($name, array $specs)
    {
        return isset($specs[$name])
            ? $specs[$name]
            : $this->blockDefaults[$name];
    }
}
