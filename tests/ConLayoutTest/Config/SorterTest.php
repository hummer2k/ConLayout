<?php
namespace ConLayoutTest\Config;

use ConLayout\Config\Sorter;
use ConLayoutTest\AbstractTest;
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class SorterTest extends AbstractTest
{
    public function testPriorities()
    {
        $sorter = new Sorter(array(
            'default' => -20,
            '\\'        => 0,
            '/'         => function($handle, $substr) {
                return substr_count($handle, $substr);
            },
            '::'        => 10
        ));
            
        $arrayToSort = array(
            'route/name' => array(),
            'default' => array(),
            'controller::action' => array(),
            'route/child/anotherchild' => array(),
            'route' => array()
        );
        $sorter->sort($arrayToSort);
        
        $expectedArray = array(
            'default' => array(),
            'route' => array(),
            'route/name' => array(),
            'route/child/anotherchild' => array(),
            'controller::action' => array()
        );
        
        $this->assertSame($expectedArray, $arrayToSort);
        
    }
}