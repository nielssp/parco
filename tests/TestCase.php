<?php
namespace Parco;

/**
 * Utility methods for unit tests.
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * Apply a parser to an input array.
     *
     * @param Parser $parser
     *            A parser.
     * @param mixed $input
     *            An input sequence.
     * @return Result Parse result.
     */
    public function apply(Parser $parser, $input = array())
    {
        return $parser->parse($input, array(1, 1));
    }
}
