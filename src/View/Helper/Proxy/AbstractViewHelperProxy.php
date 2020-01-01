<?php

namespace ConLayout\View\Helper\Proxy;

use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Helper\HelperInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
abstract class AbstractViewHelperProxy extends AbstractHelper
{
    /**
     *
     * @var HelperInterface
     */
    protected $helper;

    /**
     *
     * @param HelperInterface $helper
     */
    public function __construct(HelperInterface $helper)
    {
        $this->helper = $helper;
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->helper, $name], $arguments);
    }
}
