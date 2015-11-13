<?php
// Parco
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Parco;

/**
 * A successful parse result.
 */
class Success extends Result
{

    /**
     * Construct parse result.
     *
     * @param mixed $result
     *            Parse result.
     * @param array $nextInput
     *            Remaining input.
     * @param array $nextPos
     *            Next position.
     */
    public function __construct($result, array $nextInput, array $nextPos)
    {
        $this->successful = true;
        $this->result = $result;
        $this->nextInput = $nextInput;
        $this->nextPos = $nextPos;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return $this->result;
    }
}
