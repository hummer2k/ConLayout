<?php

declare(strict_types=1);

namespace ConLayout\Block\Factory;

use ConLayout\Exception\BadMethodCallException;
use ConLayout\Exception\InvalidBlockException;
use ConLayout\NamedParametersTrait;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\Stdlib\ArrayUtils;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Model\ViewModel;
use Psr\Container\ContainerInterface;

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
    private array $blockDefaults = [
        'capture_to' => 'content',
        'append'     => true,
        'class'      => ViewModel::class,
        'variables'  => [],
        'template'   => '',
        'actions'    => [],
    ];

    public function __construct(
        array $blockDefaults = [],
        private readonly ?ContainerInterface $blockManager = null,
    ) {
        $this->blockDefaults = ArrayUtils::merge(
            $this->blockDefaults,
            $blockDefaults,
        );
    }

    public function createBlock(string $blockId, array $specs): ModelInterface
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
                $class,
            ));
        }
        $block->setVariable('block_id', $blockId);

        return $this->configure($block, $specs);
    }

    /**
     * @inheritDoc
     */
    public function configure(ModelInterface $block, array $specs): ModelInterface
    {
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
                        $method,
                    ));
                }
            }
        }
        if ($template = $this->getOption('template', $specs)) {
            $block->setTemplate($template);
        }
        $block->setCaptureTo($this->getOption('capture_to', $specs));
        $block->setAppend($this->toBool($this->getOption('append', $specs)));
        $block->setVariable('block', $block);

        $results = $this->getEventManager()->trigger(
            __FUNCTION__ . '.post',
            $this,
            [
                'block' => $block,
                'specs' => $specs,
            ],
            function ($result) {
                return $result instanceof ModelInterface;
            },
        );
        if ($results->stopped()) {
            $block = $results->last();
        }
        return $block;
    }

    private function getOption(string $name, array $specs): mixed
    {
        return isset($specs[$name])
            ? $specs[$name]
            : $this->blockDefaults[$name];
    }

    private function toBool(mixed $value): bool
    {
        return (bool) (filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $value);
    }
}
