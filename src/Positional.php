<?php
// Parco
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Parco;

/**
 * Interface for classes with stored positional information. Used in conjunction
 * with {@see Position}.
 *
 * A position is an array of the form `array($line, $column)`, where `$line`
 * and `$column` are integers. `array(1, 1)` is always the first character of the
 * first line of an input stream.
 * There are two special cases: `array(0, 0)` represents an unknown or
 * undefined position and `array(-1, -1)` represents the end of the input.
 */
interface Positional
{

    /**
     * Get the stored position.
     *
     * @return int[]
     *            Position as a 2-element array consisting of a line number and
     *            a column number.
     */
    public function getPosition();

    /**
     * Set the stored position.
     *
     * @param int[] $pos
     *            Position as a 2-element array consisting of a line number and
     *            a column number.
     */
    public function setPosition(array $pos);
}
