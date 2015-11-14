<?php
namespace Parco;

class FailureTest extends TestCase
{

    /**
     * @expectedException \Parco\ParseException
     * @expectedExceptionMessage test
     */
    public function testGet()
    {
        $f = new Failure('test', array(1, 1), array(), array(1, 1));
        $f->get();
    }
}
