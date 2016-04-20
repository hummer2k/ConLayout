<?php
namespace ConLayout\Block;

use Zend\Http\Request;
use Zend\View\Helper\HelperInterface;
use Zend\View\Model\ModelInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
interface BlockInterface extends ModelInterface, HelperInterface
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
}
