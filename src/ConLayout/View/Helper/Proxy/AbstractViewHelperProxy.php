<?php

namespace ConLayout\View\Helper\Proxy;

use Zend\View\Helper\HelperInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
abstract class AbstractViewHelperProxy
{
    /**
     *
     * @var HelperInterface
     */
    protected $helper;

    public function __construct(HelperInterface $helper)
    {
        $this->helper = $helper;
    }
}
