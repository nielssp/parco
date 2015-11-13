<?php
// Parco
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Parco;

/**
 * An unsuccessful parse result.
 */
class Failure extends Result
{

    /**
     * Construct parse result.
     *
     * @param string $message
     *            Failure message.
     * @param array $nextInput
     *            Remaining input.
     * @param array $nextPos
     *            Next position.
     */
    public function __construct($message, array $nextInput, array $nextPos)
    {
        $this->successful = false;
        $this->message = $message;
        $this->nextInput = $nextInput;
        $this->nextPos = $nextPos;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        throw new ParseException($this);
    }
}
