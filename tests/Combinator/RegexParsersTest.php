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
        $this->skipWhitespace = false;
        
        $p1 = $this->rep($this->char('a'));
        
        $result = $this->parseAll($p1, 'aab');
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected "b", expected end of input', $result->message);
        
        $result = $this->parseAll($p1, 'aa ');
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected " ", expected end of input', $result->message);

        $result = $this->parseAll($p1, 'aa');
        $this->assertTrue($result->successful);
        $this->assertEquals(array('a', 'a'), $result->get());
        $this->assertEquals(array(), $result->nextInput);
        $this->assertEquals(array(1, 3), $result->nextPos);

        $this->skipWhitespace = true;
        
        $result = $this->parseAll($p1, 'aa ');
        $this->assertTrue($result->successful);
        $this->assertEquals(array('a', 'a'), $result->get());
        $this->assertEquals(array(), $result->nextInput);
        $this->assertEquals(array(1, 4), $result->nextPos);
    }
    
    public function testWhitespace()
    {
        $p1 = $this->whitespace();
        
        $result = $this->parse($p1, '');
        $this->assertTrue($result->successful);
        $this->assertEquals(null, $result->get());

        $result = $this->parse($p1, "\x09\x0A\x0B\x0C\x0D\x20");
        $this->assertTrue($result->successful);
        $this->assertEquals(null, $result->get());
        $this->assertEquals(array(), $result->nextInput);
        $this->assertEquals(array(2, 5), $result->nextPos);

        $result = $this->parse($p1, " \t a");
        $this->assertTrue($result->successful);
        $this->assertEquals(null, $result->get());
        $this->assertEquals(array('a'), $result->nextInput);
        $this->assertEquals(array(1, 4), $result->nextPos);

        $result = $this->parse($p1, "  \n a");
        $this->assertTrue($result->successful);
        $this->assertEquals(null, $result->get());
        $this->assertEquals(array('a'), $result->nextInput);
        $this->assertEquals(array(2, 2), $result->nextPos);
    }
    
    public function testChar()
    {
        $this->skipWhitespace = false;
        
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
        
        $p2 = $this->char("\n");
        
        $result = $this->apply($p2, array("\n"));
        $this->assertTrue($result->successful);
        $this->assertEquals("\n", $result->get());
        $this->assertEquals(array(), $result->nextInput);
        $this->assertEquals(array(2, 1), $result->nextPos);
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

        $p2 = $this->string("aa\na");
        
        $result = $this->parse($p2, "aa\na");
        $this->assertTrue($result->successful);
        $this->assertEquals("aa\na", $result->get());
        $this->assertEquals(array(), $result->nextInput);
        $this->assertEquals(array(2, 2), $result->nextPos);
    }
    
    public function testRegex()
    {
        $this->skipWhitespace = false;
        
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
