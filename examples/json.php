<?php
use Parco\Combinator\RegexParsers;
use Parco\ParseException;

include __DIR__ . '/../vendor/autoload.php';

//
// See http://json.org/ for specification
//

class Json
{
    use RegexParsers;

    /**
     *
     * @return \Parco\Parser
     */
    public function jsonNumber()
    {
        return $this->regex('/-?(0|[1-9]\d*)(\.\d+)?([eE][+-]?\d+)?/')->map(function ($x) {
            return floatval($x);
        });
    }
    
    public function jsonChar()
    {
        return $this->alt(
            $this->regex('/[^\\\\"]/'),
            $this->regex('/\\\\(["\\\\\/bfnrt]|u[0-9a-fA-F]{4})/')
                ->map(function ($escape) {
                    switch ($escape[1]) {
                        case '"':
                        case '\\':
                        case '/':
                            return $escape[1];
                        case 'b':
                            return "\x08";
                        case 'f':
                            return "\f";
                        case 'n':
                            return "\n";
                        case 'r':
                            return "\r";
                        case 't':
                            return "\t";
                        case 'u':
                            $ord = hexdec(substr($escape, 2));
                            if ($ord < 127)
                                return chr($ord);
                            return html_entity_decode('&#' . $ord . ';', ENT_NOQUOTES, 'UTF-8');
                    }
                })
        );
    }

    /**
     *
     * @return \Parco\Parser
     */
    public function jsonString()
    {
        return $this->char('"')->seqR(
            $this->noSkip(
                $this->rep($this->jsonChar)
                    ->map(function ($chars) {
                        return implode('', $chars);
                    })
                    ->seqL($this->char('"'))
            )
        );
    }

    /**
     *
     * @return \Parco\Parser
     */
    public function jsonValue()
    {
        return $this->alt(
            $this->jsonString,
            $this->jsonNumber,
            $this->jsonObject,
            $this->jsonArray,
            $this->string('true')->withResult(true),
            $this->string('false')->withResult(false),
            $this->string('null')->withResult(null)
        );
    }

    /**
     *
     * @return \Parco\Parser
     */
    public function jsonArray()
    {
        return $this->char('[')
            ->seqR($this->repsep(
                $this->jsonValue,
                $this->char(',')
            ))->seqL($this->char(']'));
    }

    /**
     *
     * @return \Parco\Parser
     */
    public function jsonObject()
    {
        return $this->char('{')
            ->seqR($this->repsep(
                $this->seq(
                    $this->jsonString->seqL($this->char(':')),
                    $this->jsonValue
                ),
                $this->char(',')
            ))->seqL($this->char('}'))
            ->map(function ($map) {
                $object = array();
                foreach ($map as $keyValue) {
                    $object[$keyValue[0]] = $keyValue[1];
                }
                return $object;
            });
    }

    /**
     *
     * @return mixed
     * @throws ParseException
     */
    public function __invoke($input)
    {
        return $this->parseAll($this->jsonValue(), $input)->get();
    }
}

$decoder = new Json();

$json = '{
    "foo": [-1.5E-2, 2, 3],
    "bar": "test\ttest"
}';

echo '<pre>';
try {
    var_dump($decoder($json));
} catch (\Parco\ParseException $e) {
    $lines = explode("\n", $json);
    $line = $e->getInputLine($lines);
    $column = $e->getInputColumn($lines);
    echo 'Syntax Error: ' . $e->getMessage() . ' on line ' . $line . ' column ' . $column . PHP_EOL;
    if ($line > 0) {
        echo $lines[$line - 1] . PHP_EOL;
        echo str_repeat('-', $column - 1) . '^';
    }
}
echo '</pre>';