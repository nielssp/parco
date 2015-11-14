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
     *            Position as a 2-element array consisting of a line number and
     *            a column number.
     */
    public function getPosition()
    {
        return $this->pos;
    }

    /**
     * Set the stored position.
     *
     * @param int[]
     *            Position as a 2-element array consisting of a line number and
     *            a column number.
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
    public function posLine()
    {
        return $this->pos[0];
    }

    /**
     * Get the stored column number.
     *
     * @return int Column number (starting from 1).
     */
    public function posColumn()
    {
        return $this->pos[1];
    }
}
