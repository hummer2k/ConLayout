<?php
namespace ConLayout\View\Renderer;

use Traversable;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\View\Exception\DomainException;
use Zend\View\Exception\InvalidArgumentException;
use Zend\View\Exception\RuntimeException;
use Zend\View\Model\ModelInterface;
use Zend\View\Renderer\PhpRenderer;


/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockRenderer
    extends PhpRenderer
    implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;
        
    /**
     * {@inheritdoc}
     */
    public function render($nameOrModel, $values = null)
    {   
        $results = $this->getEventManager()->trigger(
            __FUNCTION__ . '.pre',
            $this,
            ['block' => $nameOrModel],
            function($result) {
                return $result instanceof ModelInterface;
            }
        );

        if ($results->stopped()) {
            $rendered = $results->last();
        } else {
            $rendered = parent::render($nameOrModel, $values);
        }

        $this->getEventManager()->trigger(
            __FUNCTION__ . '.post',
            $this,
            ['__RESULT__' => $rendered]
        );
        return $rendered;
    }
    
    /**
     * {@inheritdoc}
     */
    public function __call($method, $argv)
    {
        $block = $this->plugin('viewModel')->getCurrent();
        if (method_exists($block, $method)) {
            return call_user_func_array([$block, $method], $argv);
        }
        return parent::__call($method, $argv);
    }
}
