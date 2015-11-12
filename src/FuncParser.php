<?php
// Parco
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Parco;

/**
 * A parser.
 */
class FuncParser extends Parser
{

    private $func;

    public function __construct($func)
    {
        $this->func = $func;
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function parse(array $input)
    {
        return call_user_func($this->func, $input);
    }
}
