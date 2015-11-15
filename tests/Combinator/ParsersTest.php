<?php
namespace Parco\Combinator;

use Parco\TestCase;

class ParsersTest extends TestCase
{
    use RegexParsers;
    
    private $called = 0;
    
    protected function setUp()
    {
        $this->called = 0;
    }
    
    protected function example()
    {
        $this->called++;
        return $this->success(1);
    }
    
    public function testLazyGet()
    {
        $p1 = $this->example;
        
        $this->assertEquals(0, $this->called);

        $result = $this->apply($p1);
        $this->assertTrue($result->successful);
        $this->assertEquals(1, $result->get());

        $this->assertEquals(1, $this->called);

        $result = $this->apply($p1);
        $this->assertTrue($result->successful);
        $this->assertEquals(1, $result->get());

        $this->assertEquals(1, $this->called);
    }
    
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

        $p2 = $this->elem("\n");
        
        $result = $this->apply($p2, array("\n"));
        $this->assertTrue($result->successful);
        $this->assertEquals("\n", $result->get());
        $this->assertEquals(array(), $result->nextInput);
        $this->assertEquals(array(2, 1), $result->nextPos);
        
        $result = $this->apply($p1, array('1'));
        $this->assertFalse($result->successful);

        $p3 = $this->elem(1, false);
        
        $result = $this->apply($p3, array('1'));
        $this->assertTrue($result->successful);
        $this->assertEquals(1, $result->get());
    }
    
    public function testAcceptIf()
    {
        $p1 = $this->acceptIf(function ($elem) {
            return $elem == 1;
        });
        $p2 = $this->acceptIf(function ($elem) {
            return $elem == 1;
        }, function ($elem) {
            return $elem . ' is not acceptable';
        });
        
        $result = $this->apply($p1, array());
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected end of input', $result->message);
        
        $result = $this->apply($p1, array(2));
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected "2"', $result->message);
        
        $result = $this->apply($p2, array(2));
        $this->assertFalse($result->successful);
        $this->assertEquals('2 is not acceptable', $result->message);
        
        $result = $this->apply($p1, array(1, 2));
        $this->assertTrue($result->successful);
        $this->assertEquals(1, $result->get());
        $this->assertEquals(array(2), $result->nextInput);
        $this->assertEquals(array(1, 2), $result->nextPos);
        
    }

    public function testPhrase()
    {
        $p1 = $this->elem(1);
        
        $c1 = $this->phrase($p1);
        $result = $this->apply($c1);
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected end of input, expected "1"', $result->message);
            
        $result = $this->apply($c1, array(1, 1));
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected "1", expected end of input', $result->message);
        $this->assertEquals(array(1), $result->nextInput);
        $this->assertEquals(array(1, 2), $result->nextPos);
        
        $result = $this->apply($c1, array(1));
        $this->assertTrue($result->successful);
        $this->assertEquals(1, $result->get());
        $this->assertEquals(array(), $result->nextInput);
        $this->assertEquals(array(1, 2), $result->nextPos);
    }

    public function testPositioned()
    {
        $mock = $this->getMockForTrait('Parco\Position');

        $p1 = $this->success($mock);

        $c1 = $this->positioned($p1);
        $result = $this->apply($c1);
        $this->assertTrue($result->successful);
        $this->assertEquals(array(1, 1), $result->get()->getPosition());
    }
    
    public function testOpt()
    {
        $p1 = $this->success(1);
        $p2 = $this->failure('error1');
        
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
        $p1 = $this->success(1);
        $p2 = $this->failure('error1');
        
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

        $result = $this->apply($c1, array(1));
        $this->assertTrue($result->successful);
        $this->assertEquals(array(1), $result->get());

        $result = $this->apply($c1, array(1, 2));
        $this->assertFalse($result->successful);

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
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected end of input, expected "1"', $result->message);

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
        $p1 = $this->success(1);
        $p2 = $this->success(2);
        $p3 = $this->failure('error1');
        $p4 = $this->failure('error2');

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
        $p1 = $this->success(1);
        $p2 = $this->success(2);
        $p3 = $this->failure('error1');
        $p4 = $this->failure('error2');

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
        $this->assertEquals('error1', $result->message);
    }

    public function testSuccess()
    {
        $p1 = $this->success(1);
        $result = $this->apply($p1);
        $this->assertTrue($result->successful);
        $this->assertEquals(1, $result->get());
    }

    public function testFailure()
    {
        $p1 = $this->failure('error1');
        $result = $this->apply($p1);
        $this->assertFalse($result->successful);
        $this->assertEquals('error1', $result->message);
    }
    
    public function testChainl()
    {
        $p1 = $this->elem(1);
        $p2 = $this->elem('-')->withResult(function ($left, $right) {
            return $left - $right;
        });
        
        $c1 = $this->chainl($p1, $p2);
        $result = $this->apply($c1);
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected end of input, expected "1"', $result->message);

        $result = $this->apply($c1, array(1, '-'));
        $this->assertTrue($result->successful);
        $this->assertEquals(1, $result->get());

        $result = $this->apply($c1, array(1, '-', 1, '-', 1));
        $this->assertTrue($result->successful);
        $this->assertEquals(-1, $result->get());
        $this->assertEquals(array(), $result->nextInput);
        $this->assertEquals(array(1, 6), $result->nextPos);
    }
    
    public function testChainr()
    {
        $p1 = $this->elem(1);
        $p2 = $this->elem('-')->withResult(function ($left, $right) {
            return $left - $right;
        });
        
        $c1 = $this->chainr($p1, $p2);
        $result = $this->apply($c1);
        $this->assertFalse($result->successful);
        $this->assertEquals('unexpected end of input, expected "1"', $result->message);

        $result = $this->apply($c1, array(1, '-'));
        $this->assertTrue($result->successful);
        $this->assertEquals(1, $result->get());

        $result = $this->apply($c1, array(1, '-', 1, '-', 1));
        $this->assertTrue($result->successful);
        $this->assertEquals(1, $result->get());
        $this->assertEquals(array(), $result->nextInput);
        $this->assertEquals(array(1, 6), $result->nextPos);
    }
}
