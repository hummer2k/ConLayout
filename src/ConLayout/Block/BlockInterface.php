<?php
namespace ConLayout\Block;

use Zend\Http\Request;

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
}
