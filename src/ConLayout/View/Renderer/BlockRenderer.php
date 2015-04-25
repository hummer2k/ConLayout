<?php
namespace ConLayout\View\Renderer;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\View\Renderer\PhpRenderer;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockRenderer extends PhpRenderer implements EventManagerAwareInterface
{
    use \Zend\EventManager\EventManagerAwareTrait;
        
    /**
     * {@inheritdoc}
     */
    public function render($nameOrModel, $values = null)
    {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__ . '.pre',
            $this,
            ['block' => $nameOrModel],
            function ($result) {
                return is_string($result);
            }
        );

        if ($results->stopped()) {
            return $results->last();
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
