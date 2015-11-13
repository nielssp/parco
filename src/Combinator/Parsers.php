<?php
// Parco
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Parco\Combinator;

use Parco\Parser;
use Parco\FuncParser;

/**
 * A collection of generic parser combinators.
 */
trait Parsers
{

    /**
     * Optional parser.
     *
     * `opt($p)` is a parser that always succeeds and returns `$x` if `$p` returns
     * `$x` and `null` if `$p` fails.
     *
     * @param Parser $p
     *            A parser.
     * @return FuncParser
     *            An optional parser.
     */
    public function opt(Parser $p)
    {
        return new FuncParser(function (array $input, array $pos) use ($p) {
            $r = $p->parse($input, $pos);
            if ($r->successful)
                return $r;
            return new Result(true, null, $input, $pos);
        });
    }

    /**
     * Negating parser.
     *
     * `not($p)` is a parser that fails if `$p` succeeds and succeeds if `$p`
     * fails. It never consumes any input.
     *
     * @param Parser $p
     *            A parser.
     * @return FuncParser
     *            A negating parser.
     */
    public function not(Parser $p)
    {
        return new FuncParser(function (array $input, array $pos) use ($p) {
            $r = $p->parse($input, $pos);
            if ($r->successful)
                return new Result(false, null, $input, $pos);
            return new Result(true, null, $input, $pos);
        });
    }

    /**
     * Repetition parser.
     *
     * `rep($p)` is a parser that repeatedly uses `$p` to parse the input until
     * `$p` fails. The result is an array of all results.
     *
     * @param Parser $p
     *            A parser.
     * @return FuncParser
     *            A repetition parser.
     */
    public function rep(Parser $p)
    {
        return new FuncParser(function (array $input, array $pos) use ($p) {
            $list = array();
            while (count($input)) {
                $r = $p->parse($input, $pos);
                if (!$r->successful)
                    break;
                $list[] = $r->result;
                $input = $p->nextInput;
                $pos = $p->nextPos;
            }
            return new Result(true, $list, $input, $pos);
        });
    }

    public function repsep(Parser $p, Parser $sep)
    {}

    public function rep1(Parser $p)
    {}

    public function rep1sep(Parser $p, Parser $sep)
    {}

    public function repN($num, Parser $p)
    {}

    public function seq(Parser $p, Parser $q)
    {}

    public function alt(Parser $p, Parser $q)
    {}
}