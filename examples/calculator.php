<?php
use Parco\Combinator\Parsers;
include __DIR__ . '/../vendor/autoload.php';

class Calculator
{
    use Parsers;
}

$calculator = new Calculator();