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
     * @param array $input
     *            An input array.
     * @return Result Parse result.
     */
    public function apply(Parser $parser, array $input = array())
    {
        return $parser->parse($input, array(1, 1));
    }
}
