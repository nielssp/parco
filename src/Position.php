<?php
// Parco
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Parco;

/**
 * A trait that adds a mutable position (line and column numbers).
 */
trait Position
{

    /**
     * @var int[]
     */
    private $pos = array(1, 1);

    /**
     * Get the stored position.
     *
     * @return int[]
     *            Position as a 2-element array, see {@see Positional}.
     */
    public function getPosition()
    {
        return $this->pos;
    }

    /**
     * Set the stored position.
     *
     * @param int[]
     *            Position as a 2-element array, see {@see Positional}.
     */
    public function setPosition(array $pos)
    {
        $this->pos = $pos;
    }

    /**
     * Get the stored line number.
     *
     * @return int Line number (starting from 1).
     */
    public function line()
    {
        return $this->pos[0];
    }

    /**
     * Get the stored column number.
     *
     * @return int Column number (starting from 1).
     */
    public function column()
    {
        return $this->pos[1];
    }

    /**
     * Compare two positions.
     *
     * @param int[] $a
     *            First position.
     * @param int[] $b
     *            Second position.
     * @return int 0 if the two positions are equal, a negative integer if
     *         `$b` is greater than `$a`, and a positive integer if `$a` is
     *         greater than `$b`.
     */
    public static function comparePositions(array $a, array $b)
    {
        if ($a[0] == $b[0]) {
            return $a[1] - $b[1];
        }
        return $a[0] - $b[0];
    }
}
