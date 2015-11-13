<?php
namespace Parco;

class ParserTest extends \PHPUnit_Framework_TestCase
{

    public function testAlt()
    {
        $p = new FuncParser(function (array $input, array $pos) {
            return Success(1, $input, $pos);
        });
    }
}