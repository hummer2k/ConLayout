<?php
namespace ConLayout\Block;

use Zend\View\Model\ViewModel;
/**
 * Dummy block demo
 * 
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de> *
 */
class Dummy
    extends ViewModel
{
    protected $template = 'blocks/dummy';
    
    public function init()
    {
        // do stuff        
        $this->setVariables(array(
            'title' => 'Dummy Block',
            'text' => 'This is a dummy block.'
        ));
    }    
}
