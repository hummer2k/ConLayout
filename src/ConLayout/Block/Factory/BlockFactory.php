<?php

namespace ConLayout\Block\Factory;

use ConLayout\Block\BlockInterface;
use ConLayout\Layout\LayoutInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\ModelInterface;
use Zend\EventManager\EventManagerAwareTrait;

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
     * @param array $blockDefaults
     */
    public function __construct(array $blockDefaults = [])
    {
        $this->blockDefaults = ArrayUtils::merge(
            $this->blockDefaults,
            $blockDefaults
        );
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
        if ($this->serviceLocator->has($class)) {
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
        if (false !== ($wrapper = $this->getOption('wrapper', $specs))) {
            if (is_string($wrapper)) {
                $wrapper = ['template' => $wrapper];
            } elseif (is_array($wrapper) && (!isset($wrapper['template']))) {
                $wrapper = array_merge(
                    $wrapper,
                    ['template' => 'blocks/wrapper']
                );
            }
            $originalTpl = $block->getTemplate();
            $block->setTemplate($wrapper['template']);
            if (isset($wrapper['html_class'])) {
                $block->setVariable('htmlWrapperClass', $wrapper['html_class']);
            }
            $block->setVariable('originalTpl', $originalTpl);
        }
        $block->setCaptureTo($this->getOption('capture_to', $specs));
        $block->setAppend($this->getOption('append', $specs));

        if ($block instanceof BlockInterface) {
            $block->setRequest($this->serviceLocator->get('Request'));
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
