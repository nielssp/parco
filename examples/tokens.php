<?php
use Parco\Combinator\RegexParsers;
use Parco\Positional;
use Parco\Position;
use Parco\Combinator\PositionalParsers;

include __DIR__ . '/../vendor/autoload.php';

include __DIR__ . '/lexer.php';

//
// Grammar:
//
// Expr         ::=  "let" name "=" Expr "in" Expr
//                |  "\\" name "->" Expr
//                |  "if" Expr "then" Expr "else" Expr
//                |  Comparison
// Comparison   ::=  Comparison ("==" | "!=) AddSub
//                |  AddSub
// AddSub       ::=  AddSub ("+" | "-") MultDiv
//                |  MultDiv
// MultDiv      ::=  MultDiv ("*" | "/") Application
//                |  Application
// Application  ::=  Atomic {Atomic}
// Atomic       ::=  "(" Expr ")"
//                |  name
//                |  literal
//

abstract class Expr implements Positional {
    use Position;
}
class Assignment extends Expr {
    public $name;
    public $value;
    public $body;
}
class Abstraction extends Expr {
    public $name;
    public $expr;
}
class Conditional extends Expr {
    public $condition;
    public $consequent;
    public $alternative;
}
class Application extends Expr {
    public $function;
    public $parameter;
}
class Operation extends Expr {
    public $operator;
    public $left;
    public $right;
}
class Name extends Expr {
    public $name;
}
class StringNode extends Expr {
    public $value;
}
class NumberNode extends Expr {
    public $value;
}


class TokenParser
{
    use PositionalParsers;
    
    public function expr() {
        return $this->positioned($this->alt(
            $this->assignment,
            $this->abstraction,
            $this->conditional,
            $this->comparison
        ));
    }
    
    public function assignment() {
        return $this->seq(
            $this->keyword('let')->seqR($this->name),
            $this->operator('=')->seqR($this->expr),
            $this->keyword('in')->seqR($this->expr)
        )->map(function ($parts) {
            $node = new Assignment();
            $node->name = $parts[0]->name;
            $node->value = $parts[1];
            $node->body = $parts[2];
            return $node;
        });
    }
    
    public function abstraction() {
        return $this->seq(
            $this->operator('\\')->seqR($this->name),
            $this->operator('->')->seqR($this->expr)
        )->map(function ($parts) {
            $node = new Abstraction();
            $node->name = $parts[0]->name;
            $node->expr = $parts[1];
            return $node;
        });
    }
    
    public function conditional() {
        return $this->seq(
            $this->keyword('if')->seqR($this->expr),
            $this->keyword('then')->seqR($this->expr),
            $this->keyword('else')->seqR($this->expr)
        )->map(function ($parts) {
            $node = new Conditional();
            $node->condition = $parts[0];
            $node->consequent = $parts[1];
            $node->alternative = $parts[2];
            return $node;
        });
    }
    
    public function comparison() {
        return $this->chainl($this->addSub, $this->operation(array('==', '!=')));
    }
    
    public function addSub() {
        return $this->chainl($this->multDiv, $this->operation(array('+', '-')));
    }
    
    public function multDiv() {
        return $this->chainl($this->application, $this->operation(array('*', '/')));
    }
    
    public function application() {
        return $this->rep1($this->atomic)->map(function ($seq) {
            $value = $seq[0];
            $length = count($seq);
            for ($i = 1; $i < $length; $i++) {
                $node = new Application();
                $node->function = $value;
                $node->parameter = $seq[$i];
                $value = $node;
            }
            return $value;
        });
    }
    
    public function atomic() {
        return $this->alt(
            $this->parentheses,
            $this->name,
            $this->number,
            $this->string
        );
    }
    
    public function parentheses() {
        return $this->punctuation('(')
            ->seqR($this->expr)
            ->seqL($this->punctuation(')'));
    }
    
    public function keyword($keyword) {
        return $this->acceptIf(function ($token) use ($keyword) {
            return $token->type == 'keyword' and $token->value == $keyword;
        })->withFailure('expected "' . $keyword . '"');
    }
    
    public function punctuation($punctuation) {
        return $this->acceptIf(function ($token) use ($punctuation) {
            return $token->type == 'punctuation' and $token->value == $punctuation;
        })->withFailure('expected "' . $punctuation . '"');
    }
    
    public function operator($operator) {
        return $this->acceptIf(function ($token) use ($operator) {
            return $token->type == 'operator' and $token->value == $operator;
        })->withFailure('expected "' . $operator . '"');
    }
    
    public function operation(array $ops = array()) {
        return $this->acceptIf(function ($token) use ($ops) {
            return $token->type == 'operator' and in_array($token->value, $ops);
        })->withFailure('expected opertor "' . implode('" or "', $ops) . '"')
        ->map(function ($token) {
            return function ($left, $right) use ($token) {
                $node = new Operation();
                $node->operator = $token->value;
                $node->left = $left;
                $node->right = $right;
                return $node;
            }; 
        });
    }
    
    public function name() {
        return $this->token('name')->map(function ($token) {
            $node = new Name();
            $node->name = $token->value;
            $node->setPosition($token->getPosition());
            return $node;
        });
    }
    
    public function string() {
        return $this->token('string')->map(function ($token) {
            $node = new StringNode();
            $node->value = $token->value;
            $node->setPosition($token->getPosition());
            return $node;
        });
    }
    
    public function number() {
        return $this->token('number')->map(function ($token) {
            $node = new NumberNode();
            $node->value = $token->value;
            $node->setPosition($token->getPosition());
            return $node;
        });
    }

    public function token($type) {
        return $this->acceptIf(function ($token) use ($type) {
            return $token->type == $type;
        })->withFailure('expected ' . $type);
    }

    /**
     *
     * @return Expr
     */
    public function __invoke($input)
    {
        return $this->parseAll($this->expr, $input)->get();
    }
}

$parser = new TokenParser();

echo '<pre>';

try {
    $ast = $parser($tokens);
    echo 'Abstract syntax tree:' . PHP_EOL;
    print_r($ast);
} catch (\Parco\ParseException $e) {
    echo 'Syntax Error: ' . $e->getMessage() . ' on line ' . $e->posLine() . ' column ' . $e->posColumn() . PHP_EOL;
}

echo '</pre>';