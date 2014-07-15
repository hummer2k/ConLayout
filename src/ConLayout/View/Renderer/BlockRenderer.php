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
     * Overloading: proxy to helpers and current viewModel or block instance
     *
     * Proxies to the attached plugin manager to retrieve, return, and potentially
     * execute helpers.
     *
     * * If the helper does not define __invoke, it will be returned
     * * If the helper does define __invoke, it will be called as a functor
     *
     * @param  string $method
     * @param  array $argv
     * @return mixed
     */
    public function __call($method, $argv)
    {
        $block = $this->plugin('viewModel')->getCurrent();
        if (is_callable(array($block, $method))) {
            return call_user_func_array(array($block, $method), $argv);
        }
        return parent::__call($method, $argv);
    }
}
