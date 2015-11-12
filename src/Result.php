<?php
// Parco
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Parco;

/**
 * A parse result.
 */
class Result
{

    /**
     * Whether parse was successful.
     *
     * @var bool
     */
    public $successful;

    /**
     * Result or null if unsuccessful.
     *
     * @var mixed|null
     */
    public $result;

    /**
     * Remaining input.
     *
     * @var array
     */
    public $nextInput;

    /**
     * Construct parse result.
     *
     * @param bool $successful
     *            Whether parser was successful;
     * @param mixed|null $result
     *            Result or null if unsuccessful.
     * @param array $nextInput
     *            Remaining input.
     */
    public function __construct($successful, $result, array $nextInput)
    {
        $this->successful = $successful;
        $this->result = $result;
        $this->nextInput = $nextInput;
    }

    /**
     * Get embedded result.
     *
     * @throws \Exception
     */
    public function get()
    {
        if (! isset($this->successful))
            // TODO: replace with something meaninful
            throw new \Exception('no value');
        return $this->result;
    }
}