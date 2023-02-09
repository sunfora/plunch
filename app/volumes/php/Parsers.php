<?
namespace Parsers;

require_once "/vendor/autoload.php";
use Parsica\Parsica as Parsica;


/*
 * Parses zero or up to specified number 
 */
function repeat_bounded(int $times, Parsica\Parser $parser): Parsica\Parser {
    assert($times >= 0, "bounded accepts positive or zero");

    return Parsica\Parser::make(
        "<bounded>", 
        function (Parsica\Stream $stream) use ($times, $parser) {
            $parse_result = Parsica\nothing()->run($stream);
            while ($times--) {
                $next_result = $parse_result->continueWith($parser);
                if ($next_result->isSuccess()) {
                    $parse_result = $parse_result->append($next_result);
                } else {
                    break;
                }
            }
            return $parse_result;
        }
    );
}

const MAX_STRING_LENGTH = 128;

function create_quoted_string(
    int $max_size, Parsica\Parser $quote, Parsica\Parser $element
): Parsica\Parser {
    $neutral = Parsica\pure("");
    $content = repeat_bounded($max_size, $element);
    return Parsica\between(
        $quote, $quote, $neutral->append($content)
    ); 
}

function single_quoted_string(): Parsica\Parser {
    return create_quoted_string(
        MAX_STRING_LENGTH,
        Parsica\char("'"), Parsica\anySingleBut("'")
    )->label("single_quoted_string");
}

const CHARS_TO_ESCAPE = ["\\", '"'];

function escaped_char(): Parsica\Parser {
    $escaped = Parsica\oneOf(CHARS_TO_ESCAPE);
    $parser = Parsica\keepSecond(Parsica\char("\\"), $escaped);
    return $parser;
}

function double_quoted_string(): Parsica\Parser {
    $simple_char = Parsica\noneOf(CHARS_TO_ESCAPE);
    $string_content = $simple_char->or(escaped_char());
    return create_quoted_string(
        MAX_STRING_LENGTH, 
        Parsica\char('"'), $string_content
    )->label("double_quoted_string");
}
 
function quoted_string(): Parsica\Parser {
    return Parsica\any(
        single_quoted_string(),
        double_quoted_string()
    );
}

function identifier(): Parsica\Parser {
    $parser = Parsica\atLeastOne(Parsica\alphaNumChar()->or(Parsica\oneOfS("_-")));
    return $parser->label("identifier");
}

function collect_with_spaces(...$parsers): Parsica\Parser {
    if (! $parsers) {
        return Parsica\pure([]);
    }

    $n = count($parsers);

    $add_spaces = fn ($parser) => $parser->append(Parsica\skipHSpace1());
    $mapped_parsers = array_map($add_spaces, $parsers);
    $mapped_parsers[$n - 1] = $parsers[$n - 1];
    
    return Parsica\collect(...$mapped_parsers); 
}

