<?php
// Parco
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Parco;

/**
 * A parse result.
 */
class Result implements Positional
{
    use Position;

    /**
     * Whether parse was successful.
     *
     * @var bool
     */
    public $successful;

    /**
     * Result if successful.
     *
     * @var mixed
     */
    public $result;

    /**
     * Remaining input.
     *
     * @var array
     */
    public $nextInput;

    /**
     * Failure message if unsuccessful.
     *
     * @var string|null
     */
    public $message;

    /**
     * Construct parse result.
     *
     * @param bool $successful
     *            Whether parser was successful;
     * @param mixed $result
     *            Result if successful.
     * @param array $nextInput
     *            Remaining input.
     * @param string|null $message
     *            Failure message if unsuccessful.
     */
    public function __construct($successful, $result, array $nextInput, $message = null)
    {
        $this->successful = $successful;
        $this->result = $result;
        $this->nextInput = $nextInput;
        $this->message = $message;
    }

    /**
     * Get embedded result.
     *
     * @throws \Exception
     */
    public function get()
    {
        if (! $this->successful) {
            // TODO: replace with something meaninful
            throw new \Exception('no value');
        }
        return $this->result;
    }
}
