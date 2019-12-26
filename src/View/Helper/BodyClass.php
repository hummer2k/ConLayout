<?php

namespace ConLayout\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BodyClass extends AbstractHelper
{
    /**
     *
     * @var array
     */
    protected $classes = [];

    /**
     *
     * @param type $class
     * @return BodyClass
     */
    public function __invoke($class = null)
    {
        if (null !== $class) {
            $this->addClass($class);
        }
        return $this;
    }

    /**
     *
     * @param string $class
     */
    public function removeClass($class)
    {
        $this->classes = array_diff($this->classes, [$class]);
        return $this;
    }

    /**
     *
     * @param string $class
     * @return BodyClass
     */
    public function addClass($class)
    {
        $this->classes[] = $class;
        return $this;
    }

    /**
     *
     * @return string class names space separated
     */
    public function __toString()
    {
        return implode(' ', array_unique($this->classes));
    }
}
