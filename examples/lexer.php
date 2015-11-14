<?php
use Parco\Combinator\RegexParsers;
use Parco\Positional;
use Parco\Position;

include __DIR__ . '/../vendor/autoload.php';

//
// Grammar:
//
// program      ::=  {skip | token}
// token        ::=  keyword | operator | punctuation | name | literal
// skip         ::=  {whitespace | comment}
// whitespace   ::=  " " | "\t" | "\r" | "\n"
// comment      ::=  "//" {any - "\n"}
//                |  "/*" {any - "*/"} "*/"
// keyword      ::=  "let" | "in" | "if" | "then" | "else"
// operator     ::=  "->" | "==" | "!=" | "\\" | "+" | "-"
//                |  "/" | "*" | "="
// punctuation  ::=  "(" | ")"
// name         ::=  letter {letter | digit | "_"} - keyword
// letter       ::=  "a" | ... | "z"
//                |  "A" | ... | "Z"
// digit        ::=  "0" | ... | "9"
// literal      ::=  number | string
// number       ::=  digit {digit} ["." digit {digit}]
// string       ::=  "\"" {char} "\""
// char         ::=  any - ("\\" | "\"")
//                |  "\\" any
//

class Token implements Positional {
    use Position;
    
    public $type;
    public $value;
    
    public function __construct($type, $value) {
        $this->type = $type;
        $this->value = $value;
    }
    
    public function __toString() {
        return $this->type . '(' . $this->value . ')';
    }
}

class Lexer
{
    use RegexParsers;
    
    public function __construct() {
        $this->skipWhitespace = false;
    }
    
    public function stringLiteral() {
        return $this->char('"')
            ->seqR($this->regex('/([^"\\]|\\.)*/m'))
            ->seqL($this->char('"'))
            ->map(function ($string) {
                return new Token('string', $string);
            });
    }
    
    public function numberLiteral() {
        return $this->regex('/\d+(\.\d+)?/')
            ->map(function ($number) {
                return new Token('number', floatval($number));
            });
    }
    
    public function operator() {
        return $this->regex('/->|==|!=|\\\\|\+|-|\/|\*|=/')
            ->map(function ($op) {
               return new Token('operator', $op); 
            });
    }

    public function punctuaction() {
        return $this->regex('/\(|\)/')
            ->map(function ($op) {
                return new Token('punctuation', $op);
            });
    }
    
    private $keywords = array('let', 'in', 'if', 'then', 'else');
    
    public function name() {
        return $this->regex('/[a-zA-Z][a-zA-Z0-9_]*/')
            ->map(function ($name) {
               if (in_array($name, $this->keywords)) {
                   return new Token('keyword', $name);
               } else {
                   return new Token('name', $name);
               }
            });
    }
    
    public function singleComment() {
        return $this->regex('/\/\/.*$')->withResult(null);
    }
    
    public function multiComment() {
        return $this->regex('/\/\*((?!\*\/).)*\/\*/m')->withResult(null);
    }
    
    public function skip() {
        return $this->alt(
            $this->whitespace(),
            $this->singleComment,
            $this->multiComment
        );
    }
    
    public function token() {
        return $this->alt(
            $this->operator,
            $this->punctuaction,
            $this->name,
            $this->numberLiteral,
            $this->stringLiteral
        )->positioned();
    }
    
    public function program() {
        return $this->phrase(
            $this->skip->seqR($this->rep($this->token->seqL($this->skip)))
        );
    }

    /**
     *
     * @return Token[]
     */
    public function __invoke($input)
    {
        return $this->parseAll($this->program, $input)->get();
    }
}

$lexer = new Lexer();

echo 'Result: ';
$tokens = $lexer('let x = 5 in \y -> x + y');
foreach ($tokens as $token)
    echo ' ' . $token;
