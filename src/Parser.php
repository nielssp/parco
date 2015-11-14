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
     * @param  mixed $input
     *            Input sequence.
     * @param  int[] $pos
     *            Current position as a 2-element array consisting of a line
     *            number and a column number.
     * @return Result Parser result.
     */
    abstract public function parse($input, array $pos);

    /**
     * Alternative composition of two parsers.
     *
     * `$p->alt($q)` is a parser that uses `$p` on the input and if `$p` fails
     * uses `$q` on the same input. The parser fails if both $p or $q fail. The
     * result is the result of the first parser that succeeded.
     *
     * @param  Parser $other
     *            Other parser.
     * @return Parser An alternative composition of the input parsers.
     */
    public function alt(Parser $other)
    {
        return new FuncParser(function ($input, array $pos) use ($other) {
            $result = $this->parse($input, $pos);
            if ($result->successful) {
                return $result;
            }
            return $other->parse($input, $pos);
        });
    }

    /**
     * Parser function applcication.
     *
     * `$p->map($f)` is a parser that succeeds and returns `$f($x)` if `$p`
     * succeeds and returns `$x`. It fails if `$p` fails.
     *
     * @param  callable $f Function to apply to parser output.
     * @return Parser A parser that applies a function to the output of this
     * parser.
     */
    public function map(callable $f)
    {
        return new FuncParser(function ($input, array $pos) use ($f) {
            $result = $this->parse($input, $pos);
            if (! $result->successful) {
                return $result;
            }
            return new Success($f($result->result), $pos, $result->nextInput, $result->nextPos);
        });
    }

    /**
     * Sequential composition of two parsers.
     *
     * `$p->seq($q)` is a parser that uses `$p` on the input followed by `$q`
     * on the remaining input. The parser fails if either $p or $q fails. The
     * result is an array containing both results.
     *
     * @param  Parser $other
     *            Other parser.
     * @return Parser A sequential composition of the input parsers.
     */
    public function seq(Parser $other)
    {
        return new FuncParser(function ($input, array $pos) use ($other) {
            $a = $this->parse($input, $pos);
            if (! $a->successful) {
                return $a;
            }
            $b = $other->parse($a->nextInput, $a->nextPos);
            if (! $b->successful) {
                return $b;
            }
            return new Success(
                array(
                $a->result,
                $b->result
                ),
                $pos,
                $b->nextInput,
                $b->nextPos
            );
        });
    }

    /**
     * Sequential composition of two parsers returning only the left result.
     *
     * `$p->seq($q)` is a parser that uses `$p` on the input followed by `$q`
     * on the remaining input. The parser fails if either $p or $q fails. The
     * result is the result of `$p`.
     *
     * @param  Parser $other
     *            Other parser.
     * @return Parser A sequential composition of the input parsers.
     */
    public function seqL(Parser $other)
    {
        return new FuncParser(function ($input, array $pos) use ($other) {
            $a = $this->parse($input, $pos);
            if (! $a->successful) {
                return $a;
            }
            $b = $other->parse($a->nextInput, $a->nextPos);
            if (! $b->successful) {
                return $b;
            }
            return new Success($a->result, $pos, $b->nextInput, $b->nextPos);
        });
    }

    /**
     * Sequential composition of two parsers returning only the right result.
     *
     * `$p->seq($q)` is a parser that uses `$p` on the input followed by `$q`
     * on the remaining input. The parser fails if either $p or $q fails. The
     * result is the result of `$q`.
     *
     * @param  Parser $other
     *            Other parser.
     * @return Parser A sequential composition of the input parsers.
     */
    public function seqR(Parser $other)
    {
        return new FuncParser(function ($input, array $pos) use ($other) {
            $a = $this->parse($input, $pos);
            if (! $a->successful) {
                return $a;
            }
            $b = $other->parse($a->nextInput, $a->nextPos);
            if (! $b->successful) {
                return $b;
            }
            return new Success($b->result, $pos, $b->nextInput, $b->nextPos);
        });
    }

    /**
     * Change the failure message produced by this parser.
     *
     * `$p->withFailure($m)` fails if `$p` fails, then replaces `$p`'s failure
     * message with `$m` instead.
     *
     * @param string $message
     *            The new failure message.
     * @return Parser A parser with the new failure message.
     */
    public function withFailure($message)
    {
        return new FuncParser(function ($input, array $pos) use ($message) {
            $r = $this->parse($input, $pos);
            if ($r->successful) {
                return $r;
            }
            return new Failure($message, $r->getPosition(), $r->nextInput, $r->nextPos);
        });
    }

    /**
     * Change the value returned by this parser.
     *
     * `$p->withResult($x)` succeeds if `$p` succeds, then discards `$p`'s
     * result and returns `$x` instead.
     *
     * @param mixed $result
     *            The new result.
     * @return Parser A parser with the new result.
     */
    public function withResult($result)
    {
        return new FuncParser(function ($input, array $pos) use ($result) {
            $r = $this->parse($input, $pos);
            if (! $r->successful) {
                return $r;
            }
            return new Success($result, $r->getPosition(), $r->nextInput, $r->nextPos);
        });
    }
}
