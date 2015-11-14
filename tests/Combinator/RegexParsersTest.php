<?php
namespace Parco\Combinator;

use Parco\TestCase;

class RegexParsersTest extends TestCase
{
    use RegexParsers;
    
    public function testParse()
    {
        $p1 = $this->rep($this->char('a'));
        
        $result = $this->parse($p1, 'aab');
        $this->assertTrue($result->successful);
        $this->assertEquals(array('a', 'a'), $result->get());
        $this->assertEquals(array('b'), $result->nextInput);
        $this->assertEquals(array(1, 3), $result->nextPos);
    }
    
    public function testParseAll()
    {
        $p1 = $this->rep($this->char('a'));
        
        $result = $this->parseAll($p1, 'aab');
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected "b", expected end of input', $result->message);

        $result = $this->parseAll($p1, 'aa');
        $this->assertTrue($result->successful);
        $this->assertEquals(array('a', 'a'), $result->get());
        $this->assertEquals(array(), $result->nextInput);
        $this->assertEquals(array(1, 3), $result->nextPos);
    }
    
    public function testChar()
    {
        $p1 = $this->char('a');
        
        $result = $this->apply($p1, array());
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected end of input, expected "a"', $result->message);
        
        $result = $this->apply($p1, array('b'));
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected "b", expected "a"', $result->message);
        
        $result = $this->apply($p1, array('a', 'b'));
        $this->assertTrue($result->successful);
        $this->assertEquals('a', $result->get());
        $this->assertEquals(array('b'), $result->nextInput);
        $this->assertEquals(array(1, 2), $result->nextPos);
    }
    
    public function testString()
    {
        $p1 = $this->string('aaa');
        
        $result = $this->parse($p1, '');
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected end of input, expected "a"', $result->message);

        $result = $this->parse($p1, 'aab');
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected "b", expected "a"', $result->message);

        $result = $this->parse($p1, 'aaa');
        $this->assertTrue($result->successful);
        $this->assertEquals('aaa', $result->get());
        $this->assertEquals(array(), $result->nextInput);
        $this->assertEquals(array(1, 4), $result->nextPos);
    }
    
    public function testRegex()
    {
        $p1 = $this->regex('/[ab]+/');
        
        $result = $this->parse($p1, '');
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected end of input', $result->message);
        
        $result = $this->parse($p1, ' a');
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected " "', $result->message);

        $result = $this->parse($p1, 'aabbaa');
        $this->assertTrue($result->successful);
        $this->assertEquals('aabbaa', $result->get());
        $this->assertEquals(array(), $result->nextInput);
        $this->assertEquals(array(1, 7), $result->nextPos);
    }
    
    public function testGroup()
    {
        $p1 = $this->regex('/a(b)?a+/');
        
        $c1 = $this->group(1, $p1);
        $result = $this->parse($c1, '');
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected end of input', $result->message);
        
        $result = $this->parse($c1, 'aa');
        $this->assertTrue($result->successful);
        $this->assertEquals(null, $result->get());

        $result = $this->parse($c1, 'aba');
        $this->assertTrue($result->successful);
        $this->assertEquals('b', $result->get());
        $this->assertEquals(array(1, 2), $result->getPosition());
        $this->assertEquals(array(), $result->nextInput);
        $this->assertEquals(array(1, 4), $result->nextPos);
    }
}
