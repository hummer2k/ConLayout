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
    public function testGetTtl()
    {
        $block = new BlockDummy();
        $this->assertFalse($block->getCacheTtl());
    }

    public function testCacheKeyInfo()
    {
        $block = new BlockDummy();
        $block->setTemplate('path/to/template');

        $expectedCacheKeyInfo = [
            'path/to/template',
            'ConLayoutTest\Block\BlockDummy'
        ];

        $this->assertEquals($expectedCacheKeyInfo, $block->getCacheKeyInfo());

        $expectedCacheKey = AbstractBlock::CACHE_KEY_PREFIX .
            md5(implode('|', $expectedCacheKeyInfo));

        $this->assertEquals($expectedCacheKey, $block->getCacheKey());

    }

    public function testAddCacheKeyInfo()
    {
        $block = new BlockDummy();
        $block->setTemplate('path/to/template');

        $expectedCacheKeyInfo = [
            'path/to/template',
            'ConLayoutTest\Block\BlockDummy',
            'some-info'
        ];

        $block->addCacheKeyInfo('some-info');

        $this->assertEquals($expectedCacheKeyInfo, $block->getCacheKeyInfo());

        $expectedCacheKey = AbstractBlock::CACHE_KEY_PREFIX .
            md5(implode('|', $expectedCacheKeyInfo));

        $this->assertEquals($expectedCacheKey, $block->getCacheKey());
    }

    public function testSetCacheKeyInfo()
    {
        $block = new BlockDummy();
        $block->setTemplate('path/to/template');

        $expectedCacheKeyInfo = [
            'path/to/template',
            'ConLayoutTest\Block\BlockDummy',
            'some-info',
            'some-more-info'
        ];

        $block->setCacheKeyInfo([
            'some-info',
            'some-more-info'
        ]);

        $this->assertEquals($expectedCacheKeyInfo, $block->getCacheKeyInfo());

        $expectedCacheKey = AbstractBlock::CACHE_KEY_PREFIX .
            md5(implode('|', $expectedCacheKeyInfo));

        $this->assertEquals($expectedCacheKey, $block->getCacheKey());
    }

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
