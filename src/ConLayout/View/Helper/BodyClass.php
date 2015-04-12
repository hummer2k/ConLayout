<?php
namespace ConLayout\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BodyClass extends AbstractHelper
{
    protected $classes = [];
    
    /**
     * 
     * @param type $classname
     * @return BodyClass
     */
    public function __invoke($classname = null)
    {
        if (null !== $classname) {
            $this->addClass($classname);
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
     * @param string $classname
     * @return BodyClass
     */
    public function addClass($classname)
    {
        $this->classes[] = $classname;
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
