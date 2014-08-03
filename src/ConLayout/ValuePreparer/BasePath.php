<?php
namespace ConLayout\ValuePreparer;
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BasePath implements ValuePreparerInterface
{
    protected $basePathHelper;
    
    /**
     * 
     * @param \Zend\View\Helper\BasePath $basePathHelper
     */
    public function __construct(\Zend\View\Helper\BasePath $basePathHelper)
    {
        $this->basePathHelper = $basePathHelper;
    }
    
    /**
     * 
     * @param string $value asset url to prepare
     * @return string prepared asset url
     */
    public function prepare($value)
    {
        $value = trim($value);
        if (!preg_match('#^(https?://|//)#', $value)) {
            return $this->basePathHelper->__invoke($value);
        }
        return $value;
    }
}
