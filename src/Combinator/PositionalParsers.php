<?php
// Parco
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Parco\Combinator;

use Parco\Parser;

/**
 * An extension of {@see Parsers} for parsing arrays of
 * {@see Parco\Positional}s, e.g. an array of tokens.
 */
trait PositionalParsers
{
    use Parsers;
    
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
        if (isset($tail[0])) {
            $pos = $tail[0]->getPosition();
        } else {
            $pos = array(-1, -1);
        }
        return array($tail, $pos);
    }

    /**
     * {@inheritdoc}
     */
    protected function show($element)
    {
        if (method_exists($element, '__toString')) {
            return $element->__toString();
        }
        return get_class($element);
    }

    /**
     * Use a parser to parse an input sequence.
     *
     * @param Parser $p
     *            A parser.
     * @param Positional[] $input
     *            An input sequence.
     * @return \Parco\Result Parse result.
     */
    public function parse(Parser $p, array $input)
    {
        if ($this->atEnd($input)) {
            $pos = array(1, 1);
        } else {
            $pos = $this->head($input)->getPosition();
        }
        return $p->parse($input, $pos);
    }

    /**
     * Use a parser to parse an input sequence. The entire sequence must be
     * consumed by the parser.
     *
     * `parseAll($p)` is the same as `parse(phrase($p))`.
     *
     * @param  Parser $p
     *            A parser.
     * @param Positional[] $input
     *            An input sequence.
     * @return \Parco\Result Parse result.
     */
    public function parseAll(Parser $p, array $input)
    {
        return $this->parse($this->phrase($p), $input);
    }
}
