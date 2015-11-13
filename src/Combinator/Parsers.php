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
 * A collection of generic parser combinators.
 */
trait Parsers
{

    /**
     * A parser that accepts only the given element.
     *
     * `elem($e)` is a parser that succeeds if the first element in the input
     * is equal to `$e`.
     *
     * @param mixed $e
     *            An element.
     * @return FuncParser An element parser.
     */
    public function elem($e)
    {
        return new FuncParser(function (array $input, array $pos) use ($e) {
            if (! count($input)) {
                return new Failure(
                    'unexpected end of input, expected "' . $e . '"',
                    $pos, $input, $pos
                );
            }
            if ($input[0] != $e) {
                return new Failure(
                    'unexpected "' . $input[0] . '", expected "' . $e . '"',
                    $pos, $input, $pos
                );
            }
            $input = array_slice($input, 1);
            $nextPos = $pos;
            $nextPos[1]++;
            return new Success($e, $pos, $input, $nextPos);
        });
    }

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
            return new Success(null, $pos, $input, $pos);
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
                return new Failure(null, $pos, $input, $pos);
            return new Success(null, $pos, $input, $pos);
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
            $nextPos = $pos;
            while (true) {
                $r = $p->parse($input, $nextPos);
                if (! $r->successful)
                    break;
                $list[] = $r->result;
                $input = $r->nextInput;
                $nextPos = $r->nextPos;
            }
            return new Success($list, $pos, $input, $nextPos);
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
                return new Success($list, $pos, $input, $pos);
            $list[] = $r->result;
            $input = $r->nextInput;
            $nextPos = $r->nextPos;
            while (true) {
                $s = $sep->parse($input, $nextPos);
                if (! $s->successful)
                    break;
                $r = $p->parse($s->nextInput, $s->nextPos);
                if (! $r->successful)
                    break;
                $list[] = $r->result;
                $input = $r->nextInput;
                $nextPos = $r->nextPos;
            }
            return new Success($list, $pos, $input, $nextPos);
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
            $nextPos = $pos;
            do {
                $r = $p->parse($input, $nextPos);
                if (! $r->successful) {
                    if (! count($list))
                        return $r;
                    break;
                }
                $list[] = $r->result;
                $input = $r->nextInput;
                $nextPos = $r->nextPos;
            } while (true);
            return new Success($list, $pos, $input, $nextPos);
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
            $nextPos = $r->nextPos;
            while (true) {
                $s = $sep->parse($input, $nextPos);
                if (! $s->successful)
                    break;
                $r = $p->parse($s->nextInput, $s->nextPos);
                if (! $r->successful)
                    break;
                $list[] = $r->result;
                $input = $r->nextInput;
                $nextPos = $r->nextPos;
            }
            return new Success($list, $pos, $input, $nextPos);
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
            $nextPos = $pos;
            for ($i = 0; $i < $num; $i++) {
                $r = $p->parse($input, $nextPos);
                if (! $r->successful)
                    return $r;
                $list[] = $r->result;
                $input = $r->nextInput;
                $nextPos = $r->nextPos;
            }
            return new Success($list, $pos, $input, $nextPos);
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
            $nextPos = $pos;
            foreach ($parsers as $p) {
                $r = $p->parse($input, $nextPos);
                if (! $r->successful)
                    return $r;
                $list[] = $r->result;
                $input = $r->nextInput;
                $nextPos = $r->nextPos;
            }
            return new Success($list, $pos, $input, $nextPos);
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
    
    /**
     * A parser that always succeeds.
     * 
     * @param mixed $result Parse result.
     * @return FuncParser A parser.
     */
    public function success($result) {
        return new FuncParser(function (array $input, array $pos) use ($result) {
            return new Success($result, $pos, $input, $pos);
        });
    }
    
    /**
     * A parser that always fails.
     * 
     * @param string $message Failure message.
     * @return FuncParser A parser.
     */
    public function failure($message) {
        return new FuncParser(function (array $input, array $pos) use ($message) {
            return new Failure($message, $pos, $input, $pos);
        });
    }
}
