<?php
namespace Parco;

class FailureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Parco\ParseException
     * @expectedExceptionMessage test
     */
    public function testGet()
    {
        $f = new Failure('test', array(), array(1, 1));
        $f->get();
    }
}
