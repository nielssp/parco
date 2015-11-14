<?php
namespace Parco;

use Parco\Combinator\Parsers;

class ParserTest extends TestCase
{
    use Parsers;

    public function testAlt()
    {
        $p1 = $this->success(1);
        $p2 = $this->success(2);
        $p3 = $this->failure('error1');
        $p4 = $this->failure('error2');

        $c1 = $p1->alt($p2);
        $result = $this->apply($c1);
        $this->assertTrue($result->successful);
        $this->assertEquals(1, $result->get());
        
        $c2 = $p1->alt($p3);
        $result = $this->apply($c2);
        $this->assertTrue($result->successful);
        $this->assertEquals(1, $result->get());

        $c3 = $p3->alt($p2);
        $result = $this->apply($c3);
        $this->assertTrue($result->successful);
        $this->assertEquals(2, $result->get());

        $c4 = $p3->alt($p4);
        $result = $this->apply($c4);
        $this->assertFalse($result->successful);
        $this->assertEquals('error2', $result->message);
    }
    
    public function testMap()
    {
        $p1 = $this->success(1);
        $p2 = $this->failure('error1');
        
        $f = function ($x) {
            return $x + 4;
        };
        
        $m1 = $p1->map($f);
        $result = $this->apply($m1);
        $this->assertTrue($result->successful);
        $this->assertEquals(5, $result->get());
        
        $m2 = $p2->map($f);
        $result = $this->apply($m2);
        $this->assertFalse($result->successful);
        $this->assertEquals('error1', $result->message);
    }

    public function testSeq()
    {
        $p1 = $this->success(1);
        $p2 = $this->success(2);
        $p3 = $this->failure('error1');
        $p4 = $this->failure('error2');

        $c1 = $p1->seq($p2);
        $result = $this->apply($c1);
        $this->assertTrue($result->successful);
        $this->assertEquals(array(1, 2), $result->get());
        
        $c2 = $p1->seq($p3);
        $result = $this->apply($c2);
        $this->assertFalse($result->successful);
        $this->assertEquals('error1', $result->message);

        $c3 = $p3->seq($p4);
        $result = $this->apply($c3);
        $this->assertFalse($result->successful);
        $this->assertEquals('error1', $result->message);
    }

    public function testSeqL()
    {
        $p1 = $this->success(1);
        $p2 = $this->success(2);
        $p3 = $this->failure('error1');
        $p4 = $this->failure('error2');

        $c1 = $p1->seqL($p2);
        $result = $this->apply($c1);
        $this->assertTrue($result->successful);
        $this->assertEquals(1, $result->get());
        
        
        $c2 = $p1->seqL($p3);
        $result = $this->apply($c2);
        $this->assertFalse($result->successful);
        $this->assertEquals('error1', $result->message);

        $c3 = $p3->seqL($p4);
        $result = $this->apply($c3);
        $this->assertFalse($result->successful);
        $this->assertEquals('error1', $result->message);
    }

    public function testSeqR()
    {
        $p1 = $this->success(1);
        $p2 = $this->success(2);
        $p3 = $this->failure('error1');
        $p4 = $this->failure('error2');

        $c1 = $p1->seqR($p2);
        $result = $this->apply($c1);
        $this->assertTrue($result->successful);
        $this->assertEquals(2, $result->get());
        
        $c2 = $p1->seqR($p3);
        $result = $this->apply($c2);
        $this->assertFalse($result->successful);
        $this->assertEquals('error1', $result->message);

        $c3 = $p3->seqR($p4);
        $result = $this->apply($c3);
        $this->assertFalse($result->successful);
        $this->assertEquals('error1', $result->message);
    }

    public function testWithFailure()
    {
        $p1 = $this->success(1);
        $p2 = $this->failure('error1');

        $c1 = $p1->withFailure('error2');
        $result = $this->apply($c1);
        $this->assertTrue($result->successful);
        $this->assertEquals(1, $result->get());

        $c2 = $p2->withFailure('error2');
        $result = $this->apply($c2);
        $this->assertFalse($result->successful);
        $this->assertEquals('error2', $result->message);
    }

    public function testWithResult()
    {
        $p1 = $this->success(1);
        $p2 = $this->failure('error1');

        $c1 = $p1->withResult(2);
        $result = $this->apply($c1);
        $this->assertTrue($result->successful);
        $this->assertEquals(2, $result->get());

        $c2 = $p2->withResult(2);
        $result = $this->apply($c2);
        $this->assertFalse($result->successful);
        $this->assertEquals('error1', $result->message);
    }
}
