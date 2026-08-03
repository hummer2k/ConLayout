<?php

declare(strict_types=1);

namespace ConLayout\View\Helper\Proxy;

use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Helper\HelperInterface;

abstract class AbstractViewHelperProxy extends AbstractHelper
{
    protected HelperInterface $helper;

    public function __construct(HelperInterface $helper)
    {
        $this->helper = $helper;
    }

    public function __call(string $name, array $arguments): mixed
    {
        return call_user_func_array([$this->helper, $name], $arguments);
    }
}
