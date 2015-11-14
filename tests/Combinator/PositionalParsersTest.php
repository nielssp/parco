<?php
namespace Parco\Combinator;

use Parco\TestCase;

class PositionalParsersTest extends TestCase
{
    use PositionalParsers;
    
    public function testParse()
    {
        $mock = $this->getMockForTrait('Parco\Position', array(), '', false, false, true, array('__toString'));
        $mock->setPosition(array(2, 2));
        
        $p1 = $this->elem($mock);
        
        $result = $this->parse($p1, array());
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected end of input, expected ', $result->message);

        $result = $this->parse($p1, array($mock));
        $this->assertTrue($result->successful);
        $this->assertEquals($mock, $result->get());
        $this->assertEquals(array(), $result->nextInput);
        $this->assertEquals(array(2, 2), $result->getPosition());
    }
    
    public function testParseAll()
    {
        $mock = $this->getMockForTrait('Parco\Position');
        $mock->setPosition(array(2, 2));
        
        $p1 = $this->rep($this->elem($mock));
        
        $result = $this->parseAll($p1, array());
        $this->assertTrue($result->successful);

        $result = $this->parse($p1, array($mock, $mock));
        $this->assertTrue($result->successful);
        $this->assertEquals(array($mock, $mock), $result->get());
        $this->assertEquals(array(), $result->nextInput);
        $this->assertEquals(array(2, 2), $result->getPosition());
    }
}
