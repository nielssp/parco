<?php
// Parco
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Parco;

/**
 * Interface for classes with stored positional information. Used in conjunction
 * with {@see Position}.
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
     * @param int[]
     *            Position as a 2-element array consisting of a line number and
     *            a column number.
     */
    public function setPosition(array $pos);
}
