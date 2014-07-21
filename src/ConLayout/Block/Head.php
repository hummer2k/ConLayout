<?php
namespace ConLayout\Block;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Head extends AbstractBlock
{
    public function getCss()
    {        
        return array(
            '/css/styles.css',
            '/css/main.css'
        );
    }
}
