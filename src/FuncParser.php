<?php
// Parco
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Parco;

/**
 * A parser constructed from a parse function.
 */
class FuncParser extends Parser
{

    /**
     * @var callable
     */
    private $func;

    /**
     * Construct parser from parse function.
     * 
     * @param callable $func
     *            Parse function, see {@see parse} for expected function
     *            signature.
     */
    public function __construct(callable $func)
    {
        $this->func = $func;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $input, array $pos)
    {
        return call_user_func($this->func, $input, $Pos);
    }
}
