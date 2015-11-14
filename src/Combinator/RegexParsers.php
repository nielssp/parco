<?php
// Parco
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Parco\Combinator;

use Parco\Parser;
use Parco\FuncParser;
use Parco\Success;
use Parco\Failure;


/**
 * A collection of parser combinators for string/character parsing using 
 * regular expressions.
 */
trait RegexParsers
{
    use Parsers;

    /**
     * Use a character parser to parse a string.
     *
     * @param Parser $p
     *            A parser.
     * @param string $string
     *            An input string.
     * @return \Parco\Result Parse result.
     */
    public function parse(Parser $p, $string)
    {
        $input = preg_split('//', $string, -1, PREG_SPLIT_NO_EMPTY);
        return $p->parse($input, array(1, 1));
    }

    /**
     * Use a character parser to parse a string, the entire string must be
     * parsed.
     *
     * `parseAll($p)` is the same as `parse(phrase($p))`.
     *
     * @param Parser $p
     *            A parser.
     * @param string $string
     *            An input string.
     * @return \Parco\Result Parse result.
     */
    public function parseAll(Parser $p, $string)
    {
        $input = preg_split('//', $string, -1, PREG_SPLIT_NO_EMPTY);
        return $this->phrase($p)->parse($input, array(1, 1));
    }
    
    /**
     * A parser that accepts only the given character.
     *
     * `char($c)` is a parser that succeeds if the first character in the input
     * is equal to `$c`.
     *
     * @param string $c
     *            A character.
     * @return FuncParser A character parser.
     */
    public function char($c) {
        return $this->elem($c);
    }

    /**
     * A parser that accepts only the given string.
     *
     * `string($s)` is a parser that succeeds if the first $n characters of the
     * input is equal to `$s`, where `$n=strlen($s)`.
     *
     * @param string $s
     *            A string.
     * @return FuncParser A string parser.
     */
    public function string($s) {
        return new FuncParser(function (array $input, array $pos) use ($s) {
            $length = strlen($s);
            for ($i = 0; $i < $length; $i++) {
                if (! isset($input[$i])) {
                    return new Failure(
                        'unexpected end of input, expected "' . $s[$i] . '"',
                        $pos, $input, $pos
                    );
                }
                if ($input[$i] != $s[$i]) {
                    return new Failure(
                        'unexpected "' . $input[$i] . '", expected "' . $s[$i] . '"',
                        $pos, $input, $pos
                    );
                }
            }
            $input = array_slice($input, $length);
            $nextPos = $pos;
            $nextPos[1] += $length;
            return new Success($s, $pos, $input, $nextPos);
        });
    }

    /**
     * A parser that matches a regular expression string
     *
     * @param string $r
     *            A regular expression, see {@see preg_match}.
     * @return FuncParser A regex parser.
     */
    public function regex($r) {
        return new FuncParser(function (array $input, array $pos) use ($r) {
            $ret = preg_match($r, implode('', $input), $matches, PREG_OFFSET_CAPTURE);
            if ($ret !== 1 or $matches[0][1] !== 0) {
                if (! count($input))
                    return new Failure('unexpected end of input', $pos, $input, $pos);
                return new Failure('unexpected "' . $input[0] . '"', $pos, $input, $pos);
            }
            $match = $matches[0][0];
            $length = strlen($match);
            $input = array_slice($input, $length);
            $nextPos = $pos;
            $nextPos[1] += $length;
            return new Success($match, $pos, $input, $nextPos);
        });
    }
}
