<?php

namespace ConLayoutTest\ValuePreparer;

use ConLayoutTest\AbstractTest;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class CacheBusterTest extends AbstractTest
{
    public function testFactory()
    {
        $factory = new \ConLayout\ValuePreparer\CacheBusterFactory();
        $this->assertInstanceOf(
            'ConLayout\ValuePreparer\CacheBuster',
            $factory->createService($this->sm)
        );
    }

    public function testMd5File()
    {
        $cacheBuster = new \ConLayout\ValuePreparer\CacheBuster(
            __DIR__ . '/../_files'
        );

        $value = $cacheBuster->prepare('styles.css');
        $this->assertEquals('styles.css?1688c8210b6509d702b1adb96bc4d0f3', $value);
    }

    public function testFileDoesNotExist()
    {
        $cacheBuster = new \ConLayout\ValuePreparer\CacheBuster(
                'DOES_NOT_EXIST_____'
        );
        $value = $cacheBuster->prepare('styles.css');
        $this->assertEquals('styles.css', $value);
    }
}