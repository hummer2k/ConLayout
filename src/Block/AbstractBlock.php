<?php
namespace ConLayout\Block;

use Zend\Http\Request;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\RendererInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
abstract class AbstractBlock extends ViewModel implements BlockInterface
{
    /**
     *
     * @var Request
     */
    protected $request;

    /**
     *
     * @var RendererInterface
     */
    protected $view;

    /**
     *
     * @param \Zend\Http\Request $request
     * @return AbstractBlock
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     *
     * @return Request
     */
    public function getRequest()
    {
        if (null === $this->request) {
            $this->request = new Request();
        }
        return $this->request;
    }

    /**
     *
     * @param RendererInterface $view
     * @return AbstractBlock
     */
    public function setView(RendererInterface $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     *
     * @return RendererInterface
     */
    public function getView()
    {
        return $this->view;
    }
}
