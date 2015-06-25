<?php

namespace ConLayout\Block\Factory;

use ConLayout\Block\BlockInterface;
use ConLayout\Layout\LayoutInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\ModelInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockFactory implements
    BlockFactoryInterface,
    ServiceLocatorAwareInterface,
    EventManagerAwareInterface
{
    use ServiceLocatorAwareTrait;
    use EventManagerAwareTrait;

    /**
     *
     * @var array
     */
    protected $blockDefaults = [
        'capture_to' => 'content',
        'append'     => true,
        'class'      => 'Zend\View\Model\ViewModel',
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
     *
     * @param array $blockDefaults
     * @param ServiceLocatorInterface $blockManager
     */
    public function __construct(
        array $blockDefaults = [],
        ServiceLocatorInterface $blockManager = null
    ) {
        $this->blockDefaults = ArrayUtils::merge(
            $this->blockDefaults,
            $blockDefaults
        );
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
        } else if ($this->serviceLocator->has($class)) {
            $block = $this->serviceLocator->get($class);
        } else {
            $block = new $class();
        }
        $block->setVariable(LayoutInterface::BLOCK_ID_VAR, $blockId);
        foreach ($this->getOption('options', $specs) as $name => $option) {
            $block->setOption($name, $option);
        }
        foreach ($this->getOption('variables', $specs) as $name => $variable) {
            $block->setVariable($name, $variable);
        }
        foreach ($this->getOption('actions', $specs) as $method => $params) {
            if (is_callable([$block, $method])) {
                call_user_func_array([$block, $method], $params);
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
        if (is_string($options)) {
            $options = ['template' => $options];
        } elseif (is_array($options) && (!isset($options['template']))) {
            $options = array_merge(
                $options,
                ['template' => 'blocks/wrapper']
            );
        }
        $originalTpl = $block->getTemplate();
        $block->setTemplate($options['template']);
        if (isset($options['html_class'])) {
            $block->setVariable('htmlWrapperClass', $options['html_class']);
        }
        if (isset($options['html_tag'])) {
            $block->setVariable('htmlWrapperTag', $options['html_tag']);
        }
        $block->setVariable('originalTpl', $originalTpl);
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
