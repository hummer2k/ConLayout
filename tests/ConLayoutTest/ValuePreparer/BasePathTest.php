<?php
namespace ConLayoutTest\ValuePreparer;

use ConLayout\ValuePreparer\BasePath;
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BasePathTest extends \ConLayoutTest\AbstractTest
{   
    public function getBasePath()
    {
        $basePathHelper = $this->sm->get('viewHelperManager')
            ->get('basePath');
        $basePathHelper->setBasePath('/');
        return new BasePath($basePathHelper);
    }

    public function testPrepareRelativeUrl()
    {
        $basePath = $this->getBasePath();
        $value = 'css/styles.css';        
        $result = $basePath->prepare($value);
        
        $this->assertSame('/css/styles.css', $result);
    }
    
    public function testPrepareAbsoluteUrls()
    {
        $basePath = $this->getBasePath();
        $result = array(
            '//example.org/css/main.css',
            'https://www.example.com/js/script.js',
            'http://www.example.com/img/test.png'
        );
        foreach ($result as $value) {
            $this->assertSame($value, $basePath->prepare($value));
        }
    }
}
