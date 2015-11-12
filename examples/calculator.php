<?php
use Parco\Combinator\RegexParsers;

include __DIR__ . '/../vendor/autoload.php';

class Calculator
{
    use RegexParsers;

    public function number()
    {
        // TODO: implement
    }

    public function factor()
    {
        // TODO: implement
    }

    public function term()
    {
        // TODO: implement
    }

    public function expr()
    {
        // TODO: implement
    }

    public function __invoke($input)
    {
        $result = $this->parseAll($this->expr(), $input);
        if ($result->successful)
            return $result->get();
        trigger_error('Parse error: ' . $result->error, E_USER_ERROR);
    }
}

$calculator = new Calculator();

echo $calculator("2 + 2 * 5 - 4 / 2");