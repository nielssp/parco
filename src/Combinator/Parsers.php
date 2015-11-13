<?php
// Parco
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Parco\Combinator;

use Parco\Parser;
use Parco\FuncParser;
use Parco\Result;

/**
 * A collection of generic parser combinators.
 */
trait Parsers
{

    /**
     * Optional parser.
     *
     * `opt($p)` is a parser that always succeeds and returns `$x` if `$p`
     * returns `$x` and `null` if `$p` fails.
     *
     * @param Parser $p
     *            A parser.
     * @return FuncParser An optional parser.
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
     * @return FuncParser A negating parser.
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
     * @return FuncParser A repetition parser.
     */
    public function rep(Parser $p)
    {
        return new FuncParser(function (array $input, array $pos) use ($p) {
            $list = array();
            while (true) {
                $r = $p->parse($input, $pos);
                if (! $r->successful)
                    break;
                $list[] = $r->result;
                $input = $r->nextInput;
                $pos = $r->nextPos;
            }
            return new Result(true, $list, $input, $pos);
        });
    }

    /**
     * Interleaved repetition parser.
     *
     * `repsep($p, $sep)` is a parser that repeatedly uses `$p` interleaved with
     * `$sep` to parse the input until `$p` fails. The result is an array of all
     * results of `$p`.
     *
     * @param Parser $p
     *            A parser.
     * @param Parser $sep
     *            A parser that parses the elements that separate the elements
     *            parsed by `$p`.
     * @return FuncParser A repetition parser.
     */
    public function repsep(Parser $p, Parser $sep)
    {
        return new FuncParser(function (array $input, array $pos) use ($p, $sep) {
            $list = array();
            $r = $p->parse($input, $pos);
            if (! $r->successful)
                return new Result(true, $list, $input, $pos);
            $list[] = $r->result;
            $input = $r->nextInput;
            $pos = $r->nextPos;
            while (true) {
                $s = $sep->parse($input, $pos);
                if (! $s->successful)
                    break;
                $r = $p->parse($s->nextInput, $s->nextPos);
                if (! $r->successful)
                    break;
                $list[] = $r->result;
                $input = $r->nextInput;
                $pos = $r->nextPos;
            }
            return new Result(true, $list, $input, $pos);
        });
    }

    /**
     * Non-empty repetition parser.
     *
     * `rep1($p)` is a parser that repeatedly uses `$p` to parse the input until
     * `$p` fails. It fails if the first use of `$p` fails. The result is an
     * array of all results.
     *
     * @param Parser $p
     *            A parser.
     * @return FuncParser A repetition parser.
     */
    public function rep1(Parser $p)
    {
        return new FuncParser(function (array $input, array $pos) use ($p) {
            $list = array();
            do {
                $r = $p->parse($input, $pos);
                if (! $r->successful) {
                    if (! count($list))
                        return $r;
                    break;
                }
                $list[] = $r->result;
                $input = $r->nextInput;
                $pos = $r->nextPos;
            } while (true);
            return new Result(true, $list, $input, $pos);
        });
    }

    /**
     * Non-empty interleaved repetition parser.
     *
     * `repsep($p, $sep)` is a parser that repeatedly uses `$p` interleaved with
     * `$sep` to parse the input until `$p` fails. It fails if the first use of
     * `$p` fails. The result is an array of all results of `$p`.
     *
     * @param Parser $p
     *            A parser.
     * @param Parser $sep
     *            A parser that parses the elements that separate the elements
     *            parsed by `$p`.
     * @return FuncParser A repetition parser.
     */
    public function rep1sep(Parser $p, Parser $sep)
    {
        return new FuncParser(function (array $input, array $pos) use ($p, $sep) {
            $list = array();
            $r = $p->parse($input, $pos);
            if (! $r->successful)
                return $r;
            $list[] = $r->result;
            $input = $r->nextInput;
            $pos = $r->nextPos;
            while (true) {
                $s = $sep->parse($input, $pos);
                if (! $s->successful)
                    break;
                $r = $p->parse($s->nextInput, $s->nextPos);
                if (! $r->successful)
                    break;
                $list[] = $r->result;
                $input = $r->nextInput;
                $pos = $r->nextPos;
            }
            return new Result(true, $list, $input, $pos);
        });
    }

    /**
     * N-repetitions parser.
     *
     * `repN($n, $p)` is a parser that uses `$p` to parse the input exactly `$n`
     * times. It fails if any of the uses of `$p` fails. The result is an array
     * of all results.
     *
     * @param int $num
     *            Number of repetitions.
     * @param Parser $p
     *            A parser.
     * @return FuncParser A repetition parser.
     */
    public function repN($num, Parser $p)
    {
        return new FuncParser(function (array $input, array $pos) use ($num, $p) {
            $list = array();
            for ($i = 0; $i < $num; $i++) {
                $r = $p->parse($input, $pos);
                if (! $r->successful)
                    return $r;
                $list[] = $r->result;
                $input = $r->nextInput;
                $pos = $r->nextPos;
            }
            return new Result(true, $list, $input, $pos);
        });
    }

    /**
     * Sequential composition of two or more parsers.
     *
     * `seq($p, $q)` is a parser that uses `$p` on the input followed by `$q`
     * on the remaining input. The parser fails if either $p or $q fails. The
     * result is an array of all results.
     *
     * Additional parameters are accepted such that:
     * `seq($p, $q, $r) = seq($p, seq($q, $r))`.
     *
     * @param Parser $p
     *            First parser.
     * @param Parser $q
     *            Second parser.
     * @param Parser $r,...
     *            Any additional parsers.
     * @return FuncParser A sequential composition of the input parsers.
     */
    public function seq(Parser $p, Parser $q)
    {
        $parsers = func_get_args();
        return new FuncParser(function (array $input, array $pos) use ($parsers) {
            $list = array();
            foreach ($parsers as $p) {
                $r = $p->parse($input, $pos);
                if (! $r->successful)
                    return $r;
                $list[] = $r->result;
                $input = $r->nextInput;
                $pos = $r->nextPos;
            }
            return new Result(true, $list, $input, $pos);
        });
    }

    /**
     * Alternative composition of two or more parsers.
     *
     * `alt($p, $q)` is a parser that uses `$p` on the input and if `$p` fails
     * uses `$q` on the same input. The parser fails if both $p or $q fail. The
     * result is the result of the first parser that succeeded.
     *
     * An arbitrary number of additional parameters are accepted such that:
     * `alt($p, $q, $r) = alt($p, alt($q, $r))`.
     *
     * @param Parser $p
     *            First parser.
     * @param Parser $q
     *            Second parser.
     * @param Parser $r,...
     *            Any additional parsers.
     * @return FuncParser An alternative composition of the input parsers.
     */
    public function alt(Parser $p, Parser $q)
    {
        $parsers = func_get_args();
        return new FuncParser(function (array $input, array $pos) use ($parsers) {
            foreach ($parsers as $p) {
                $r = $p->parse($input, $pos);
                if ($r->successful)
                    return $r;
                $input = $r->nextInput;
                $pos = $r->nextPos;
            }
            return $r;
        });
    }
}
