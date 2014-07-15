<?php
namespace ConLayout\Block;

use Zend\View\Model\ViewModel;
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Head extends ViewModel
{
    public function getCss()
    {
        return array(
            '/css/styles.css',
            '/css/main.css'
        );
    }
}
