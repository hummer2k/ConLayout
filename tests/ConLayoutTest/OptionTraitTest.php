<?php
namespace ConLayoutTest;
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class OptionTraitTest extends AbstractTest
{
    use \ConLayout\OptionTrait;
    
    protected $options = array(
        'lorem' => array(
            'ipsum' => 20
        ),
        'dolor' => array(
            'blubb' => array(
                'test' => 'my_option_value'
            )
        ),
        'test' => array(
            'bla/blubb' => array(
                'val' => 'Test1234'
            )
        )
    );
    
    public function testGetOption()
    {        
        $ipsum = $this->getOption($this->options, 'lorem/ipsum');
        $this->assertSame($ipsum, 20);
        
        $blubb = $this->getOption($this->options, 'dolor/blubb');
        $this->assertSame($blubb, array(
            'test' => 'my_option_value'
        ));
        
        $test = $this->getOption($this->options, 'dolor/blubb/test');
        $this->assertSame($test, 'my_option_value');        
    }
    

    public function testGetOptionDefault()
    {
        $nonExistingValue = $this->getOption($this->options, 'does/not/exist');
        $this->assertNull($nonExistingValue);     
        
        $nonExistingValue = $this->getOption($this->options, 'does/not/exist', 'default');
        $this->assertSame('default', $nonExistingValue);
    }
    
    public function testDelimiter()
    {
        $value = $this->getOption($this->options, 'dolor.blubb.test', null, '.');
        $this->assertSame('my_option_value', $value);
    }
    
    public function testEscape()
    {
        $value = $this->getOption($this->options, 'test/bla/blubb/val');
        $this->assertNull($value);
        
        $value = $this->getOption($this->options, 'test/bla\/blubb/val');
        $this->assertSame('Test1234', $value);
    }
}
