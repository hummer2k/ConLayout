<?php

namespace ConLayout\Filter;

use Zend\Filter\FilterInterface;
use Zend\View\Helper\BasePath as BasePathHelper;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BasePathFilter implements FilterInterface
{
    /**
     *
     * @var BasePathHelper $basePathHelper
     */
    protected $basePathHelper;

    /**
     *
     * @param BasePathHelper $basePathHelper
     */
    public function __construct(BasePathHelper $basePathHelper)
    {
        $this->basePathHelper = $basePathHelper;
    }

    /**
     *
     * @param string $value asset url to prepare
     * @return string prepared asset url
     */
    public function filter($value)
    {
        $urlHost = parse_url($value, PHP_URL_HOST);
        if (empty($urlHost)) {
            return call_user_func($this->basePathHelper, $value);
        }
        return $value;
    }
}
