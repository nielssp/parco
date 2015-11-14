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
     * @param int[]  $pos
     *            Failure position.
     * @param array  $nextInput
     *            Remaining input.
     * @param int[]  $nextPos
     *            Next position.
     */
    public function __construct($message, array $pos, array $nextInput, array $nextPos)
    {
        $this->successful = false;
        $this->message = $message;
        $this->setPosition($pos);
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
