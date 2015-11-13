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
     * @param array $pos
     *            Current position as a 2-element array consisting of a line
     *            number and a column number.
     * @return Result Parser result.
     */
    public abstract function parse(array $input, array $pos);

    public function alt(Parser $other)
    {
        return new FuncParser(function (array $input, array $pos) use ($other) {
            $result = $this->parse($input, $pos);
            if ($result->successful)
                return $result;
            return $other->parse($input, $pos);
        });
    }

    public function map(callable $f)
    {
        return new FuncParser(function (array $input, array $pos) use ($f) {
            $result = $this->parse($input, $pos);
            if (! $result->successful)
                return $result;
            return new Result(true, $f($result->result), $result->nextInput, $result->nextPos);
        });
    }

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
