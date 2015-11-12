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
     * Apply parser to input.
     *
     * @return Result Parser result.
     */
    public abstract function parse(array $input);

    public function alt(Parser $other)
    {
        return new FuncParser(function (array $input) use($other) {
            $result = $this->parse($input);
            if ($result->successful)
                return $result;
            return $other->parse($input);
        });
    }

    public function conc(Parser $other)
    {
        return new FuncParser(function (array $input) use($other) {
            $a = $this->parse($input);
            if (! $a->successful)
                return $a;
            $b = $other->parse($a->nextInput);
            if (! $b->successful)
                return $b;
            return new Result(true, array($a->result, $b->result), $b->nextInput);
        });
    }

    public function concL(Parser $other)
    {
        return new FuncParser(function (array $input) use($other) {
            $a = $this->parse($input);
            if (! $a->successful)
                return $a;
            $b = $other->parse($a->nextInput);
            if (! $b->successful)
                return $b;
            return new Result(true, $a->result, $b->nextInput);
        });
    }

    public function concR(Parser $other)
    {
        return new FuncParser(function (array $input) use($other) {
            $a = $this->parse($input);
            if (! $a->successful)
                return $a;
            $b = $other->parse($a->nextInput);
            if (! $b->successful)
                return $b;
            return new Result(true, $b->result, $b->nextInput);
        });
    }
}
