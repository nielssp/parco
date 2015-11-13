<?php
// Parco
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Parco;

/**
 * A parser.
 */
abstract class Parser
{

    /**
     * Apply parser to input sequence.
     *
     * @param array $input
     *            Input sequence.
     * @param int[] $pos
     *            Current position as a 2-element array consisting of a line
     *            number and a column number.
     * @return Result Parser result.
     */
    public abstract function parse(array $input, array $pos);

    /**
     * Alternative composition of two parsers.
     *
     * `$p->alt($q)` is a parser that uses `$p` on the input and if `$p` fails
     * uses `$q` on the same input. The parser fails if both $p or $q fail. The
     * result is the result of the first parser that succeeded.
     *
     * @param Parser $other
     *            Other parser.
     * @return FuncParser An alternative composition of the input parsers.
     */
    public function alt(Parser $other)
    {
        return new FuncParser(function (array $input, array $pos) use ($other) {
            $result = $this->parse($input, $pos);
            if ($result->successful)
                return $result;
            return $other->parse($input, $pos);
        });
    }

    /**
     * Parser function applcication.
     * 
     * `$p->map($f)` is a parser that succeeds and returns `$f($x)` if `$p`
     * succeeds and returns `$x`. It fails if `$p` fails.
     * 
     * @param callable $f Function to apply to parser output.
     * @return FuncParser A parser that applies a function to the output of
     * this parser.
     */
    public function map(callable $f)
    {
        return new FuncParser(function (array $input, array $pos) use ($f) {
            $result = $this->parse($input, $pos);
            if (! $result->successful)
                return $result;
            return new Result(true, $f($result->result), $result->nextInput, $result->nextPos);
        });
    }

    /**
     * Sequential composition of two parsers.
     *
     * `$p->seq($q)` is a parser that uses `$p` on the input followed by `$q`
     * on the remaining input. The parser fails if either $p or $q fails. The
     * result is an array containing both results.
     *
     * @param Parser $other
     *            Other parser.
     * @return FuncParser A sequential composition of the input parsers.
     */
    public function seq(Parser $other)
    {
        return new FuncParser(function (array $input, array $pos) use ($other) {
            $a = $this->parse($input);
            if (! $a->successful)
                return $a;
            $b = $other->parse($a->nextInput, $a->nextPos);
            if (! $b->successful)
                return $b;
            return new Result(true, array(
                $a->result,
                $b->result
            ), $b->nextInput, $b->nextPos);
        });
    }

    /**
     * Sequential composition of two parsers returning only the left result.
     *
     * `$p->seq($q)` is a parser that uses `$p` on the input followed by `$q`
     * on the remaining input. The parser fails if either $p or $q fails. The
     * result is the result of `$p`.
     *
     * @param Parser $other
     *            Other parser.
     * @return FuncParser A sequential composition of the input parsers.
     */
    public function seqL(Parser $other)
    {
        return new FuncParser(function (array $input, array $pos) use ($other) {
            $a = $this->parse($input);
            if (! $a->successful)
                return $a;
            $b = $other->parse($a->nextInput, $a->nextPos);
            if (! $b->successful)
                return $b;
            return new Result(true, $a->result, $b->nextInput, $b->nextPos);
        });
    }

    /**
     * Sequential composition of two parsers returning only the right result.
     *
     * `$p->seq($q)` is a parser that uses `$p` on the input followed by `$q`
     * on the remaining input. The parser fails if either $p or $q fails. The
     * result is the result of `$q`.
     *
     * @param Parser $other
     *            Other parser.
     * @return FuncParser A sequential composition of the input parsers.
     */
    public function seqR(Parser $other)
    {
        return new FuncParser(function (array $input, array $pos) use ($other) {
            $a = $this->parse($input);
            if (! $a->successful)
                return $a;
            $b = $other->parse($a->nextInput, $a->nextPos);
            if (! $b->successful)
                return $b;
            return new Result(true, $b->result, $b->nextInput, $b->nextPos);
        });
    }
}
