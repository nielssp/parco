<?php
// Parco
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Parco\Combinator;

trait Parsers
{
    public function opt(Parser $p) {
        return new FuncParser(function (array $input) use($p) {
            $r = $p->parse($input);
            if ($r->successful)
                return $r;
            return new Result(true, null, $input);
        });
    }

    public function not(Parser $p) {
        return new FuncParser(function (array $input) use($p) {
            $r = $p->parse($input);
            if ($r->successful)
                return new Result(false, null, $input);
            return new Result(true, null, $input);
        });
    }

    public function rep(Parser $p) {
        return new FuncParser(function (array $input) use($p) {
            $list = array();
            while (count($input)) {
                $r = $p->parse($input);
                if (!$r->successful)
                    break;
                $list[] = $r->result;
                $input = $p->nextInput;
            }
            return new Result(true, $list, $input);
        });
    }

    public function repsep(Parser $p, Parser $sep) {
    
    }

    public function rep1(Parser $p) {
    
    }


    public function rep1sep(Parser $p, Parser $sep) {
    
    }

    public function repN($num, Parser $p) {
    
    }
}