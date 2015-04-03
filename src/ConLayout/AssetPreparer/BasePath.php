<?php
namespace ConLayout\AssetPreparer;

use ConLayout\AssetPreparer\AssetPreparerInterface;
use Zend\View\Helper\BasePath as BasePathHelper;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BasePath implements AssetPreparerInterface
{
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
    public function prepare($value)
    {
        $value = trim($value);
        if (!preg_match('#^(https?://|//)#', $value)) {
            return call_user_func($this->basePathHelper, $value);
        }
        return $value;
    }
}
