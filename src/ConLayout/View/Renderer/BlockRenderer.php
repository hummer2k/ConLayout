<?php
namespace ConLayout\View\Renderer;

use Zend\View\Renderer\PhpRenderer;
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockRenderer
    extends PhpRenderer
{
    /**
     * Overloading: proxy to Variables container 
     * if method getName() of block exists it will pull the data 
     * from the block  
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);
        $block = $this->plugin('viewModel')->getCurrent();
        if (is_callable(array($block, $method))) {
            return $block->{$method}();
        }
        return parent::__get($name);
    }
}
