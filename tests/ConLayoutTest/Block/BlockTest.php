<?php

namespace ConLayoutTest\Block;

use ConLayout\Block\AbstractBlock;
use ConLayoutTest\AbstractTest;
use Zend\Http\PhpEnvironment\Request;
use Zend\Stdlib\RequestInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockTest extends AbstractTest
{
    public function testSetRequest()
    {
        $block = new BlockDummy();
        $request = new Request();

        $block->setRequest($request);

        $this->assertSame($request, $block->getRequest());
    }

    public function testGetRequestFromNull()
    {
        $block = new BlockDummy();
        $this->assertInstanceOf(RequestInterface::class, $block->getRequest());
    }
}
// @codingStandardsIgnoreStart
class BlockDummy extends AbstractBlock
{

}
// @codingStandardsIgnoreEnd
