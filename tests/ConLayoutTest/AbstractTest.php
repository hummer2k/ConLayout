<?php
namespace ConLayoutTest;

use Zend\View\Resolver\TemplateMapResolver;
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{
    protected function getResolver()
    {
        return new TemplateMapResolver([
            'widget1' => __DIR__ . '/view/widget1.phtml',
            'layout' => __DIR__ . '/view/layout.phtml',
            'widget-content' => __DIR__ . '/view/widget-content.phtml',
            'widget-content-after' => __DIR__ . '/view/widget-content-after.phtml'
        ]);
    }
}
