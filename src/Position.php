<?php
// Parco
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Parco;

/**
 * A trait that adds a mutable position (line and column numbers). Implements
 * the interface {@see Positional}.
 */
trait Position
{

    /**
     * @var int[]
     */
    private $pos = [0, 0];

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
     * @deprecated Use {@see getPosition} or {@see getInputLine} depending on
     *             context.
     */
    public function line()
    {
        return $this->pos[0];
    }

    /**
     * Get the stored column number.
     *
     * @return int Column number (starting from 1).
     * @deprecated Use {@see getPosition} or {@see getInputColumn} depending on
     *             context.
     */
    public function column()
    {
        return $this->pos[1];
    }

    /**
     * Get the line number for an array of lines.
     * Returns `0` if the position is unknown, or the sequence of lines is
     * empty.
     * Returns the last line number if position is at the end of the input.
     *
     * @param string[] $lines
     *            List of lines (e.g. result of `explode("\n", $input)`).
     * @return int The positive line number or 0 if unknown.
     * @since 1.1.0
     */
    public function getInputLine($lines)
    {
        if (! count($lines) or $this->pos[0] == 0) {
            return 0;
        }
        if ($this->pos[0] < 0) {
            return count($lines);
        }
        return $this->pos[0];
    }

    /**
     * Get the column number for an array of lines.
     * Returns `0` if the position is unknown, or the sequence of lines is
     * empty.
     * If the position is at the end of the input,
     *
     * @param string[] $lines
     *            List of lines (e.g. result of `explode("\n", $input)`).
     * @return int The positive column number or 0 if unknown.
     * @since 1.1.0
     */
    public function getInputColumn($lines)
    {
        if (! count($lines) or $this->pos[0] == 0) {
            return 0;
        }
        if ($this->pos[0] < 0) {
            return strlen($lines[count($lines) - 1]) + 1;
        }
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
        if ($a[0] < 0) {
            if ($b[0] < 0) {
                return 0;
            }
            return 1;
        }
        if ($b[0] < 0) {
            return -1;
        }
        if ($a[0] == $b[0]) {
            return $a[1] - $b[1];
        }
        return $a[0] - $b[0];
    }
}
