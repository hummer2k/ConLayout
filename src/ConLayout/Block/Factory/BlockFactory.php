<?php

namespace ConLayout\Block\Factory;

use ConLayout\Block\BlockInterface;
use ConLayout\Exception\BadMethodCallException;
use ConLayout\Exception\InvalidBlockException;
use ConLayout\Layout\LayoutInterface;
use ConLayout\NamedParametersTrait;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\ModelInterface;
use Zend\View\Model\ViewModel;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockFactory implements
    BlockFactoryInterface,
    EventManagerAwareInterface
{
    const WRAPPER_DEFAULT = 'blocks/wrapper';

    use EventManagerAwareTrait;
    use NamedParametersTrait;

    /**
     *
     * @var array
     */
    protected $blockDefaults = [
        'capture_to' => 'content',
        'append'     => true,
        'class'      => ViewModel::class,
        'options'    => [],
        'variables'  => [],
        'template'   => '',
        'actions'    => [],
        'wrapper'    => false
    ];

    /**
     *
     * @var ServiceLocatorInterface
     */
    protected $blockManager;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     *
     * @param array $blockDefaults
     * @param ServiceLocatorInterface $blockManager
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(
        array $blockDefaults = [],
        ServiceLocatorInterface $blockManager = null,
        ServiceLocatorInterface $serviceLocator = null
    ) {
        $this->blockDefaults = ArrayUtils::merge(
            $this->blockDefaults,
            $blockDefaults
        );
        $this->blockManager = $blockManager;
        $this->serviceLocator = $serviceLocator;
    }

    /**
     *
     * @param string $blockId
     * @param array $specs
     * @return ModelInterface
     */
    public function createBlock($blockId, array $specs)
    {
        $this->getEventManager()->trigger(
            __METHOD__ . '.pre',
            $this,
            [
                'block_id' => $blockId,
                'specs' => $specs
            ]
        );
        /* @var $block ModelInterface */
        $class = $this->getOption('class', $specs);
        if (null !== $this->blockManager && $this->blockManager->has($class)) {
            $block = $this->blockManager->get($class);
        } elseif (class_exists($class)) {
            $block = new $class();
        } else {
            throw new InvalidBlockException(sprintf(
                'Block "%s" could not be instantiated. Class does not exist.',
                $class
            ));
        }
        $block->setVariable(LayoutInterface::BLOCK_ID_VAR, $blockId);
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
        if (false !== ($wrapperOptions = $this->getOption('wrapper', $specs))) {
            $this->wrapBlock($block, $wrapperOptions);
        }
        $block->setCaptureTo($this->getOption('capture_to', $specs));
        $block->setAppend($this->getOption('append', $specs));

        if ($block instanceof BlockInterface) {
            $block->setRequest($this->serviceLocator->get('Request'));
            $block->setView($this->serviceLocator->get('ViewRenderer'));
        }

        if (method_exists($block, 'init')) {
            $block->init();
        }
        $results = $this->getEventManager()->trigger(
            'createBlock.post',
            $this,
            [
                'block' => $block,
                'specs' => $specs,
                'block_id' => $blockId
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
     * assign wrapper template to block
     *
     * @param ModelInterface $block
     * @param array|string $options
     */
    protected function wrapBlock(ModelInterface $block, $options)
    {
        $attributes = $options;
        if (is_string($options)) {
            $wrapperTemplate = $options;
            $attributes = [];
        } elseif (is_array($options) && (!isset($options['template']))) {
            $wrapperTemplate = self::WRAPPER_DEFAULT;
        } else {
            $wrapperTemplate = $options['template'];
            unset($attributes['template']);
        }
        if (isset($options['tag'])) {
            $block->setVariable('wrapperTag', $options['tag']);
            unset($attributes['tag']);
        }
        $originalTemplate = $block->getTemplate();
        $block->setOption('is_wrapped', true);
        $block->setTemplate($wrapperTemplate);
        $block->setVariable('wrapperAttributes', $attributes);
        $block->setVariable('originalTemplate', $originalTemplate);
    }

    /**
     *
     * @param string $name
     * @param array $specs
     * @return mixed
     */
    protected function getOption($name, array $specs)
    {
        return isset($specs[$name])
            ? $specs[$name]
            : $this->blockDefaults[$name];
    }
}
