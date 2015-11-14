<?php
// Parco
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Parco;

/**
 * A parse result.
 */
abstract class Result implements Positional
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
    public $result = null;

    /**
     * Failure message if unsuccessful.
     *
     * @var string|null
     */
    public $message = null;

    /**
     * Remaining input.
     *
     * @var array
     */
    public $nextInput;
    
    /**
     * Next position.
     *
     * @var array
     */
    public $nextPos;

    /**
     * Get stored parse result.
     *
     * @throws ParseException If unsuccessful.
     * @return mixed Stored parse result.
     */
    abstract public function get();
}
