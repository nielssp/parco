<?php
// Parco
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Parco;

/**
 * A parse exception with positional information.
 */
class ParseException extends \RuntimeException implements Positional
{
    use Position;
    
    /**
     * Construct parse exception from a parse result.
     *
     * @param Result          $result   Parse result.
     * @param \Exception|null $previous Previous exception if any.
     */
    public function __construct(Result $result, \Exception $previous = null)
    {
        parent::__construct($result->message, 0, $previous);
        $this->setPosition($result->getPosition());
    }
}
