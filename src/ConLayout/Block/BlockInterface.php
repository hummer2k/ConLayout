<?php
namespace ConLayout\Block;

use Zend\Http\Request;
use Zend\View\Renderer\RendererInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
interface BlockInterface
{
    /**
     *
     * @param Request $request
     */
    public function setRequest(Request $request);

    /**
     *
     * @return Request
     */
    public function getRequest();

    /**
     *
     * @param RendererInterface $view
     */
    public function setView(RendererInterface $view);

    /**
     * @return RendererInterface
     */
    public function getView();
}
