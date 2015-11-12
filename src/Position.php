<?php
// Parco
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Parco;

trait Position
{
    private $pos = array(1, 1);
    
    public function getPosition() {
        return $this->pos;
    }
    
    public function setPosition($pos) {
        $this->pos = $pos;
    }
}