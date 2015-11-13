<?php
namespace Parco\Combinator;

use Parco\TestParsers;

class ParsersTest extends \PHPUnit_Framework_TestCase
{
    use TestParsers;
    use Parsers;
    
    public function testElem()
    {
        $p1 = $this->elem(1);
        
        $result = $this->apply($p1, array());
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected end of input, expected "1"', $result->message);
        
        $result = $this->apply($p1, array(2));
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected "2", expected "1"', $result->message);
        
        $result = $this->apply($p1, array(1, 2));
        $this->assertTrue($result->successful);
        $this->assertEquals(1, $result->get());
        $this->assertEquals(array(2), $result->nextInput);
        $this->assertEquals(array(1, 2), $result->nextPos);
    }
    
    public function testOpt()
    {
        $p1 = $this->successful(1);
        $p2 = $this->unsuccessful('error1');
        
        $c1 = $this->opt($p1);
        $result = $this->apply($c1);
        $this->assertTrue($result->successful);
        $this->assertEquals(1, $result->get());

        $c2 = $this->opt($p2);
        $result = $this->apply($c2);
        $this->assertTrue($result->successful);
        $this->assertNull($result->get());
    }
    
    public function testNot()
    {
        $p1 = $this->successful(1);
        $p2 = $this->unsuccessful('error1');
        
        $c1 = $this->not($p1);
        $result = $this->apply($c1);
        $this->assertFalse($result->successful);
        $this->assertEquals(null, $result->message);

        $c2 = $this->not($p2);
        $result = $this->apply($c2);
        $this->assertTrue($result->successful);
        $this->assertNull($result->get());
    }
    
    public function testRep()
    {
        $p1 = $this->elem(1);
        
        $c1 = $this->rep($p1);
        $result = $this->apply($c1);
        $this->assertTrue($result->successful);
        $this->assertEquals(array(), $result->get());

        $result = $this->apply($c1, array(1, 1, 1));
        $this->assertTrue($result->successful);
        $this->assertEquals(array(1, 1, 1), $result->get());
        $this->assertEquals(array(), $result->nextInput);
        $this->assertEquals(array(1, 4), $result->nextPos);
    }
    
    public function testRepsep()
    {
        $p1 = $this->elem(1);
        $p2 = $this->elem(2);
        
        $c1 = $this->repsep($p1, $p2);
        $result = $this->apply($c1);
        $this->assertTrue($result->successful);
        $this->assertEquals(array(), $result->get());

        $result = $this->apply($c1, array(1, 2));
        $this->assertTrue($result->successful);
        $this->assertEquals(array(1), $result->get());

        $result = $this->apply($c1, array(1, 2, 1, 2, 1));
        $this->assertTrue($result->successful);
        $this->assertEquals(array(1, 1, 1), $result->get());
        $this->assertEquals(array(), $result->nextInput);
        $this->assertEquals(array(1, 6), $result->nextPos);
    }
    
    public function testRep1()
    {
        $p1 = $this->elem(1);
        
        $c1 = $this->rep1($p1);
        $result = $this->apply($c1);
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected end of input, expected "1"', $result->message);

        $result = $this->apply($c1, array(1, 1, 1));
        $this->assertTrue($result->successful);
        $this->assertEquals(array(1, 1, 1), $result->get());
        $this->assertEquals(array(), $result->nextInput);
        $this->assertEquals(array(1, 4), $result->nextPos);
    }
    
    public function testRep1sep()
    {
        $p1 = $this->elem(1);
        $p2 = $this->elem(2);
        
        $c1 = $this->rep1sep($p1, $p2);
        $result = $this->apply($c1);
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected end of input, expected "1"', $result->message);

        $result = $this->apply($c1, array(1, 2));
        $this->assertTrue($result->successful);
        $this->assertEquals(array(1), $result->get());

        $result = $this->apply($c1, array(1, 2, 1, 2, 1));
        $this->assertTrue($result->successful);
        $this->assertEquals(array(1, 1, 1), $result->get());
        $this->assertEquals(array(), $result->nextInput);
        $this->assertEquals(array(1, 6), $result->nextPos);
    }

    public function testRepn()
    {
        $p1 = $this->elem(1);
    
        $c1 = $this->repn(2, $p1);
        $result = $this->apply($c1);
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected end of input, expected "1"', $result->message);
    
        $result = $this->apply($c1, array(1, 1, 1));
        $this->assertTrue($result->successful);
        $this->assertEquals(array(1, 1), $result->get());
        $this->assertEquals(array(1), $result->nextInput);
        $this->assertEquals(array(1, 3), $result->nextPos);
    }

    public function testSeq()
    {
        $p1 = $this->successful(1);
        $p2 = $this->successful(2);
        $p3 = $this->unsuccessful('error1');
        $p4 = $this->unsuccessful('error2');

        $c1 = $this->seq($p1, $p2);
        $result = $this->apply($c1);
        $this->assertTrue($result->successful);
        $this->assertEquals(array(1, 2), $result->get());
        
        
        $c2 = $this->seq($p1, $p3);
        $result = $this->apply($c2);
        $this->assertFalse($result->successful);
        $this->assertEquals('error1', $result->message);

        $c3 = $this->seq($p3, $p4);
        $result = $this->apply($c3);
        $this->assertFalse($result->successful);
        $this->assertEquals('error1', $result->message);
    }

    public function testAlt()
    {
        $p1 = $this->successful(1);
        $p2 = $this->successful(2);
        $p3 = $this->unsuccessful('error1');
        $p4 = $this->unsuccessful('error2');

        $c1 = $this->alt($p1, $p2);
        $result = $this->apply($c1);
        $this->assertTrue($result->successful);
        $this->assertEquals(1, $result->get());
        
        
        $c2 = $this->alt($p1, $p3);
        $result = $this->apply($c2);
        $this->assertTrue($result->successful);
        $this->assertEquals(1, $result->get());

        $c3 = $this->alt($p3, $p2);
        $result = $this->apply($c3);
        $this->assertTrue($result->successful);
        $this->assertEquals(2, $result->get());

        $c4 = $this->alt($p3, $p4);
        $result = $this->apply($c4);
        $this->assertFalse($result->successful);
        $this->assertEquals('error2', $result->message);
    }
}
