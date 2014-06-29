<?php
namespace ConLayout\Block;

use Zend\View\Model\ViewModel;
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Template
    extends ViewModel
{
    public function getLinks()
    {
        return array(
            'lorem',
            'ipsum',
            'dolor'
        );
    }
}
