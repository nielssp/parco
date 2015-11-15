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
use Parco\Match;

/**
 * A collection of parser combinators for string/character parsing using
 * regular expressions.
 *
 * This trait defines an input sequence as an array of characters.
 */
trait RegexParsers
{
    use Parsers;
    
    /**
     * If true, parsers produced by {@see char}, {@see string}, and {@see regex}
     * will skip whitespace before.
     *
     * @var bool $skipWhitespace
     */
    protected $skipWhitespace = true;

    /**
     * {@inheritdoc}
     */
    protected function atEnd($input)
    {
        return count($input) == 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function head($input)
    {
        return $input[0];
    }

    /**
     * {@inheritdoc}
     */
    protected function tail($input, array $pos)
    {
        $head = $input[0];
        $tail = array_slice($input, 1);
        if ($head === "\n") {
            $pos[0]++;
            $pos[1] = 1;
        } else {
            $pos[1]++;
        }
        return array($tail, $pos);
    }

    /**
     * {@inheritdoc}
     */
    protected function show($element)
    {
        return '"' . $element . '"';
    }

    /**
     * Use a character parser to parse a string.
     *
     * @param  Parser $p
     *            A parser.
     * @param  string $string
     *            An input string.
     * @return \Parco\Result Parse result.
     */
    public function parse(Parser $p, $string)
    {
        $input = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
        return $p->parse($input, array(1, 1));
    }

    /**
     * Use a character parser to parse a string. The entire string must be
     * consumed by the parser.
     *
     * `parseAll($p)` is the same as `parse(phrase($p))`.
     *
     * @param  Parser $p
     *            A parser.
     * @param  string $string
     *            An input string.
     * @return \Parco\Result Parse result.
     */
    public function parseAll(Parser $p, $string)
    {
        if ($this->skipWhitespace) {
            $p = $p->seqL($this->whitespace());
        }
        $p = $this->phrase($p);
        return $this->parse($p, $string);
    }
    
    /**
     * A parser that matches any number of whitespace characters.
     *
     * The following bytes are matched: 0x09 (horizontal tab), 0x0A (line feed),
     * 0x0B (vertical tab), 0x0C (form feed), 0x0D (carriage return), and 0x20
     * (space).
     *
     * @return Parser A whitespace parser.
     */
    public function whitespace()
    {
        if (! isset($this->parserCache['@ws'])) {
            $this->parserCache['@ws'] = new FuncParser(function ($input, array $pos) {
                $i = 0;
                $nextPos = $pos;
                while (true) {
                    if (! isset($input[$i])) {
                        return new Success(null, $pos, array(), $nextPos);
                    }
                    switch ($input[$i]) {
                        case "\x0A":
                            $nextPos[0]++;
                            $nextPos[1] = 1;
                            break;
                        case "\x09":
                        case "\x0B":
                        case "\x0C":
                        case "\x0D":
                        case "\x20":
                            $nextPos[1]++;
                            break;
                        default:
                            $input = array_slice($input, $i);
                            return new Success(null, $pos, $input, $nextPos);
                    }
                    $i++;
                }
            });
        }
        return $this->parserCache['@ws'];
    }
    
    /**
     * A parser that temporarily sets {@see $skipWhitespace} to false.
     *
     * @param Parser $p
     *            A parser.
     * @return \Parco\FuncParser A parser that doesn't skip whitespace.
     */
    public function noSkip(Parser $p)
    {
        return new FuncParser(function ($input, array $pos) use ($p) {
            $skip = $this->skipWhitespace;
            $this->skipWhitespace = false;
            $r = $p->parse($input, $pos);
            $this->skipWhitespace = $skip;
            return $r;
        });
    }
    
    /**
     * A parser that accepts only the given character.
     *
     * `char($c)` is a parser that succeeds if the first character in the input
     * is equal to `$c`.
     *
     * @param  string $c
     *            A character.
     * @return Parser A character parser.
     */
    public function char($c)
    {
        return new FuncParser(function ($input, array $pos) use ($c) {
            if ($this->skipWhitespace) {
                $r = $this->whitespace()->parse($input, $pos);
                $input = $r->nextInput;
                $pos = $r->nextPos;
            }
            if ($this->atEnd($input)) {
                return new Failure(
                    'unexpected end of input, expected ' . $this->show($c),
                    $pos,
                    $input,
                    $pos
                );
            }
            $head = $this->head($input);
            if ($head !== $c) {
                return new Failure(
                    'unexpected ' . $this->show($head) . ', expected ' . $this->show($c),
                    $pos,
                    $input,
                    $pos
                );
            }
            list($input, $nextPos) = $this->tail($input, $pos);
            return new Success($c, $pos, $input, $nextPos);
        });
    }

    /**
     * A parser that accepts only the given string.
     *
     * `string($s)` is a parser that succeeds if the first $n characters of the
     * input is equal to `$s`, where `$n=strlen($s)`.
     *
     * @param  string $s
     *            A string.
     * @return Parser A string parser.
     */
    public function string($s)
    {
        return new FuncParser(function ($input, array $pos) use ($s) {
            if ($this->skipWhitespace) {
                $r = $this->whitespace()->parse($input, $pos);
                $input = $r->nextInput;
                $pos = $r->nextPos;
            }
            $length = strlen($s);
            $nextPos = $pos;
            for ($i = 0; $i < $length; $i++) {
                if (! isset($input[$i])) {
                    return new Failure(
                        'unexpected end of input, expected ' . $this->show($s[$i]),
                        $pos,
                        $input,
                        $pos
                    );
                }
                if ($input[$i] !== $s[$i]) {
                    return new Failure(
                        'unexpected ' . $this->show($input[$i]) . ', expected ' . $this->show($s[$i]),
                        $pos,
                        $input,
                        $pos
                    );
                }
                if ($input[$i] === "\n") {
                    $nextPos[0]++;
                    $nextPos[1] = 1;
                } else {
                    $nextPos[1]++;
                }
            }
            $input = array_slice($input, $length);
            return new Success($s, $pos, $input, $nextPos);
        });
    }

    /**
     * A parser that matches a regular expression string.
     *
     * The parser returns an instance of {@see Match} to store its result.
     *
     * @param  string $regex
     *            A regular expression, see {@see preg_match}.
     * @return Parser A regex parser.
     */
    public function regex($regex)
    {
        return new FuncParser(function ($input, array $pos) use ($regex) {
            if ($this->skipWhitespace) {
                $r = $this->whitespace()->parse($input, $pos);
                $input = $r->nextInput;
                $pos = $r->nextPos;
            }
            $ret = preg_match($regex, implode('', $input), $matches, PREG_OFFSET_CAPTURE);
            if ($ret !== 1 or $matches[0][1] !== 0) {
                if (! count($input)) {
                    return new Failure('unexpected end of input', $pos, $input, $pos);
                }
                return new Failure('unexpected ' . $this->show($input[0]), $pos, $input, $pos);
            }
            $length = strlen($matches[0][0]);
            $input = array_slice($input, $length);
            $nextPos = $pos;
            $nextPos[1] += $length;
            return new Match($matches, $pos, $input, $nextPos);
        });
    }

    /**
     * A parser that returns the `$i`th group of a regex parse result.
     *
     * @param int $i
     *            Group number starting from 0, where 0 is the entire matched
     *            string.
     * @param Parser $p
     *            A regex parser, see {@see regex}.
     * @return Parser A parser that returns the group or null if the group is
     *         empty.
     */
    public function group($i, Parser $p)
    {
        return new FuncParser(function ($input, array $pos) use ($i, $p) {
            $r = $p->parse($input, $pos);
            if (! $r->successful) {
                return $r;
            }
            $group = $r->group($i);
            $offset = $r->offset($i);
            if (isset($offset)) {
                $pos[1] += $offset;
            }
            return new Success($group, $pos, $r->nextInput, $r->nextPos);
        });
    }
}
