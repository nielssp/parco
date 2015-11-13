<?php
namespace Parco;

/**
 * Utility method for unit tests.
 */
trait TestParsers {

    /**
     * Apply a parser to an input array.
     * 
     * @param Parser $parser A parser.
     * @param array $input An input array.
     * @return Result Parse result.
     */
    public function apply(Parser $parser, array $input = array()) {
        return $parser->parse($input, array(1, 1));
    }
    
    /**
     * Create a parser that always succeeds.
     * 
     * @param mixed $result Parse result.
     * @return FuncParser A parser.
     */
    public function successful($result) {
        return new FuncParser(function (array $input, array $pos) use ($result) {
            return new Success($result, $input, $pos);
        });
    }
    
    /**
     * Create a parser that always fails.
     * 
     * @param string $message Failure message.
     * @return FuncParser A parser.
     */
    public function unsuccessful($message) {
        return new FuncParser(function (array $input, array $pos) use ($message) {
            return new Failure($message, $input, $pos);
        });
    }
}