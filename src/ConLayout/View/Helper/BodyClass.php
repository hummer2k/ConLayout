<?php
namespace ConLayout\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BodyClass extends AbstractHelper
{
    protected $classes = array();
    
    /**
     * 
     * @param type $classname
     * @return BodyClass
     */
    public function __invoke($classname = null)
    {
        if (null !== $classname) {
            $this->append($classname);
        }
        return $this;
    }
    
    /**
     * 
     * @param type $classname
     * @return BodyClass
     */
    public function append($classname)
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
        return implode(' ', $this->classes);
    }
}
