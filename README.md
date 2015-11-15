# Parco – PHP parser combinators

Parco is an experimental parser combinator library for PHP inspired by [Scala Parser Combinators](https://github.com/scala/scala-parser-combinators). See also [Wikipedia](https://en.wikipedia.org/wiki/Parser_combinator) for general information on parser combinators.

The API documentation is available on [parco.nielssp.dk/api](http://parco.nielssp.dk/api).

## Install
Requirements:

* PHP 5.4 or newer

Install using composer:
```
composer require nielssp/parco
```
## Usage

See the `examples` directory for some examples:

* `calculator.php` is a simple calculator made using `RegexParsers` based on [this example in the Scala Parser Combinators documentation](http://www.scala-lang.org/files/archive/api/2.11.2/scala-parser-combinators/#scala.util.parsing.combinator.RegexParsers).
* `json.php` is a JSON parser using `RegexParsers`.
* `lexer.php` is a lexer/scanner for a small expression language based on the &lambda;-calculus.
* `tokens.php` is a parser using `PositionalParsers` to convert the token sequence produced by `lexer.php` into an abstract syntax tree.

### Writing a parser

To write a parser using Parco simply use on of the combinator traits in a class. There are currently three traits:

* [Parsers](http://parco.nielssp.dk/api/class-Parco.Combinator.Parsers.html) for generic parser combinators (the user must provide an input sequence implementation),
* [RegexParsers](http://parco.nielssp.dk/api/class-Parco.Combinator.RegexParsers.html) (extends `Parsers`) for parsing strings, and
* [PositionalParsers](http://parco.nielssp.dk/api/class-Parco.Combinator.PositionalParsers.html) (extends `Parsers`) for parsing arrays of objects that implement the `Positional` interface (e.g. a list of tokens from a lexer).

```php
class Myparser
{
    use \Parco\Combinator\RegexParsers;
}
```

To implement a parser you may define multiple subparsers and combine them using combinators.

Each subparser is implemented as a parameterless method returning a `Parser` object. It usually makes sense to have a method for each production rule in your language grammar, so for a grammar such as:
```nohighlight
 expr   ::= term {"+" term | "-" term}
 term   ::= factor {"*" factor | "/" factor}
 factor ::= "(" expr ")"
          | number
 number ::= digit {digit} ["." digit {digit}]
```
our parser class may have the following structure:
```php
class Myparser
{
    use \Parco\Combinator\RegexParsers;

    public function expr()
    {
    	return // a parser for expressions
    }

    public function term()
    {
    	return // a parser for terms
    }

    public function factor()
    {
    	return // a parser for factors
    }

    public function number()
    {
    	return // a parser for numbers
    }
}
```
You may also want to add a method for invoking the parser:
```php
class MyParser
{
    use \Parco\Combinator\RegexParsers;

    // ...

    public function __invoke($string)
    {
    	return $this->parseAll($this->expr, $string)->get();
    }
}
```
The `parseAll` method is provided by the `RegexParsers` trait. It converts the input string to a sequence of characters and makes sure that there is no leftover input after the given parser has been applied. Now we can use the parser by constructing and then invoking it:
```php
$myParser = new MyParser();
echo $myParser('1 + 5 - 7 / 2');
```
If the parser fails, a `ParseException` is thrown. The "Error handling" section below explains how to handle parse errors.

### Terminals

The follwing parsers can be used to parse one or more input sequence elements:

```php
$this->elem('a') // exact element
$this->acceptIf(function ($elem) { return $elem instanceof NumberToken; })) // predicate
$this->char('a') // character (RegexParsers)
$this->string('goto') // string (RegexParsers)
```
Additionally `RegexParsers` provides a method for parsing input sequence elements using regular expressions, e.g.:
```php
$this->regex('/[a-z][a-z0-9]*/i')
```
The above parsers serve as the basic building blocks for constructing more advanced parsers. The following section shows how to combine them.

### From grammar to code

Some basic combinators provided by `Parsers`:

* Sequencing: `a b c` (`a` followed by `b` followed by `c`):
```php
$this->seq($this->a, $this->b, $this->c)
```
If you want to parse `a` followed by `b`, but only want to keep the result of `a`, the method `seqL` can be used:
```php
$this->a->seqL($this->b)
```
Similarily, `seqR` can be used to keep the result of `b` instead.
* Alternation: `a | b | c` (`a`, `b`, or `c`):
```php
$this->alt($this->a, $this->b, $this->c)
```
* Repetition: `{a}` (zero or more repetitions of `a`):
```php
$this->rep($this->a)
```
* Option: `[a]` (zero or one `a`):
```php
$this->opt($this->a)
```

More combinators are described on the [API documentation page](http://parco.nielssp.dk/api/class-Parco.Combinator.Parsers.html).

### Manipulating parser results

The abstract `Parser` class provides some methods for manipulating the result of a parser. The most important one is the `map`-method, which converts the result of a parser using a function, e.g.:
```php
$this->regex('/\d+/')->map(function ($digits) {
    return intval($digits);
});
```
The above parser uses a regular expression to parse one or more digits, then converts the result to and integer.

Two other useful methods are:
* `withResult` replace the result of a parser:
```php
$this->string('true')->withResult(true);
```
* `withFailure` set a custom failure message:
```php
$this->regex('/\d+/')->withFailure('expected an integer');
```

### Recursion

The `Parsers` trait provides a magic getter that converts parameterless parsers into lazy parsers. This can be used to implement recursive grammars such as the follwing:

```nohighlight
expr ::= term "-" term
term ::= "(" expr ")"
       | number
```

To use this feature simply refererence your parser functions without parentheses (e.g. `$this->expr` instead of `$this->expr()`):

```php
public function expr()
{
    return $this->seq($this->term, $this->char("-"), $this->term);
}
public function term()
{
    return $this->alt(
    	$this->seq($this->char("("), $this->expr, $this->char(")")),
        $this->number
    );
}
```

### Left recursion

Some left-recursive grammars (e.g. left-associative operators) such as
```nohighlight
expr ::= expr "-" term
       | term
```
can be implemented using the `chainl`-combinator:
```php
public function expr()
{
    return $this->chainl(
        $this->term,
        $this->char('-')->withResult(function ($left, $right) {
            return $left - $right;
        })
    );
}
```
The second parameter to `chainl` is a parser that parses the separator (i.e. the `'-'` terminal) and returns a function that combines parse result from left to right.

Thus the result of parsing `8 - 4 - 1 - 3` with the above parser is `((8 - 4) - 1) - 3 = 0`.

A similar combinator, `chainr`, can be used for right-associative operators.

### Error handling

The result of a parser includes information about the line number and column number. This can be used to produce helpful error messages.

An example of a parser error handler:
```php
$result = $myParser($input);
if (! $result->successful) {
    echo 'Syntax Error: ' . $result->message
        . ' on line ' . $result->line()
        . ' column ' . $result->column() . PHP_EOL;
    $lines = explode("\n", $input);
    echo $lines[$result->line() - 1] . PHP_EOL;
    echo str_repeat('-', $result->column() - 1) . '^';
}
```
Which produces output such as:
```
Syntax Error: expected "->" on line 1 column 17
let x = 5 in \y - x + y
----------------^
```
Parco also has an exception class, `ParseException`, that can be used to wrap parse errors. It is thrown automatically when calling `get()` on an unsuccessful parser result.

## License
Copyright (C) 2015 Niels Sonnich Poulsen (http://nielssp.dk)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.