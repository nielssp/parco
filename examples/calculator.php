<?php
use Parco\Combinator\RegexParsers;

include __DIR__ . '/../vendor/autoload.php';

//
// Calculator grammar:
//
// expr   ::= term {"+" term | "-" term}
// term   ::= factor {"*" factor | "/" factor}
// factor ::= "(" expr ")"
//          | number
// number ::= digit {digit} ["." digit {digit}]
//

class Calculator
{
    use RegexParsers;

    /**
     *
     * @return \Parco\Parser
     */
    public function number()
    {
        return $this->regex('/\d+(\.\d+)?/')->map(function ($x) {
            return floatval($x);
        });
    }

    /**
     *
     * @return \Parco\Parser
     */
    public function factor()
    {
        $expr = $this->char('(')->seqR($this->expr)->seqL($this->char(')'));
        return $expr->alt($this->number);
    }

    /**
     *
     * @return \Parco\Parser
     */
    public function term()
    {
        return $this->factor->seq($this->rep($this->alt(
            $this->char("*")->seq($this->factor),
            $this->char("/")->seq($this->factor)
        )))->map(function ($numbers) {
            // $numbers = array(factor, array(array("*", factor), ...))
            $x = $numbers[0];
            foreach ($numbers[1] as $operation) {
                if ($operation[0] == "*") {
                    $x *= $operation[1];
                } else {
                    $x /= $operation[1];
                }
            }
            return $x;
        });
    }

    /**
     *
     * @return \Parco\Parser
     */
    public function expr()
    {
        return $this->term->seq($this->rep($this->alt(
            $this->char("+")->seq($this->term),
            $this->char("-")->seq($this->term)
        )))->map(function ($numbers) {
            // $numbers = array(term, array(array("+", term), ...))
            $x = $numbers[0];
            foreach ($numbers[1] as $operation) {
                if ($operation[0] == "+") {
                    $x += $operation[1];
                } else {
                    $x -= $operation[1];
                }
            }
            return $x;
        });
    }

    /**
     *
     * @return float
     */
    public function __invoke($input)
    {
        $result = $this->parseAll($this->expr(), $input);
        if ($result->successful) {
            return $result->get();
        }
        trigger_error(
            'Parse error: ' . $result->message
            . ' on line ' . $result->posLine()
            . ' column ' . $result->posColumn(),
            E_USER_ERROR
        );
    }
}

$calculator = new Calculator();

echo 'Result: ';
echo $calculator(' 2 + 4 / 2 - 3 * ( 6 - ( 5 + 3 ) ) ');
