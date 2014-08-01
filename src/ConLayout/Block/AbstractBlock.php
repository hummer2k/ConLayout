<?php
namespace ConLayout\Block;

use Zend\View\Model\ViewModel,
    Zend\View\HelperPluginManager,
    Zend\Http\Request;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
abstract class AbstractBlock
    extends ViewModel
    implements BlockInterface
{
    /**
     *
     * @var Request
     */
    protected $request;
    
    /**
     *
     * @var HelperPluginManager
     */
    protected $viewPluginManager;
    
    /**
     * 
     * @param type $variables
     * @param type $options
     * @param \Zend\View\HelperPluginManager $viewPluginManager
     */
    public function __construct($variables = null, $options = null, HelperPluginManager $viewPluginManager = null)
    {
        parent::__construct($variables, $options);
        $this->viewPluginManager = $viewPluginManager;
        $this->prepareView();
    }
    
    /**
     * 
     * @param string $name
     * @return mixed
     */
    protected function viewHelper($name)
    {
        if (null !== $this->viewPluginManager) {
            return $this->viewPluginManager->get($name);            
        }
    }
    
    /**
     * 
     * @param \Zend\Http\Request $request
     * @return \ConLayout\Block\AbstractBlock
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }
    
    /**
     * prepare view 
     */
    protected function prepareView()
    {
    }
}
