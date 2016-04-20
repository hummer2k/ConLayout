<?php

namespace ConLayoutTest\View\Helper;

use ConLayout\View\Helper\Wrapper;
use PHPUnit_Framework_TestCase;
use Zend\View\HelperPluginManager;
use Zend\View\Renderer\PhpRenderer;

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class WrapperTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     * @var Wrapper
     */
    private $wrapperHelper;

    public function setUp()
    {
        $this->wrapperHelper = new Wrapper();
        $view = new PhpRenderer();
        $view->setHelperPluginManager(new HelperPluginManager());
        $this->wrapperHelper->setView($view);
    }

    public function testDefaultOpeningTag()
    {
        call_user_func($this->wrapperHelper);
        $this->assertEquals(
            '<' . Wrapper::DEFAULT_TAG . '>',
            $this->wrapperHelper->openTag()
        );
    }

    public function testOpenTagHasAttributes()
    {
        $tag = 'aside';
        call_user_func($this->wrapperHelper, $tag);
        $this->assertEquals(
            '<' . $tag . ' class="col-xs-12" id="my-wrapper">',
            $this->wrapperHelper->openTag(['class' => 'col-xs-12', 'id' => 'my-wrapper'])
        );
    }

    public function testClosingTag()
    {
        call_user_func($this->wrapperHelper);
        $this->assertEquals('</div>', $this->wrapperHelper->closeTag());
    }
}
