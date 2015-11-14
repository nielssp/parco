<?php
// Parco
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Parco;

/**
 * A successful regular expression match.
 */
class Match extends Success
{
    
    /**
     * @var array
     */
    private $matches;

    /**
     * Construct regular expression result.
     *
     * @param array $matches
     *            Matches as returned by {@see preg_matches}.
     * @param int[] $pos
     *            Result position.
     * @param array $nextInput
     *            Remaining input.
     * @param int[] $nextPos
     *            Next position.
     */
    public function __construct($matches, array $pos, array $nextInput, array $nextPos)
    {
        parent::__construct($matches[0][0], $pos, $nextInput, $nextPos);
        $this->matches = $matches;
    }

    /**
     * Get the matched string in group $i.
     *
     * @param int $i
     *            Group number. 0 is the string that matched the full
     *            pattern, i.e. `get() = group(0)`.
     * @return string|null The matched string or null if empty group.
     */
    public function group($i)
    {
        if (! isset($this->matches[$i])) {
            return null;
        }
        return $this->matches[$i][0];
    }

    /**
     * Get the index of the first matched character in group $i.
     *
     * @param int $i
     *            Group number. 0 is the string that matched the full
     *            pattern.
     * @return int|null The matched offset or null if empty group.
     */
    public function offset($i)
    {
        if (! isset($this->matches[$i])) {
            return null;
        }
        return $this->matches[$i][1];
    }
}
