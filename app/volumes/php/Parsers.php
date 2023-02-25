<?
namespace Plunch\Parsers;

require "/vendor/autoload.php";
use \Parsica\Parsica as Parsica;

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

function collect_with_spaces(...$parsers): Parsica\Parser {
    if (! $parsers) {
        return Parsica\pure([]);
    }

    $n = count($parsers);

    $add_spaces = fn ($parser) => $parser->append(Parsica\skipSpace1());
    $mapped_parsers = array_map($add_spaces, $parsers);
    $mapped_parsers[$n - 1] = $parsers[$n - 1];
    
    return Parsica\collect(...$mapped_parsers); 
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

function unquoted_string(): Parsica\Parser {
    $ws = Parsica\isWhitespace();
    $quote = Parsica\orPred(
        Parsica\isEqual('"'), Parsica\isEqual("'")
    );
    $bad = Parsica\orPred($ws, $quote);
    $not_bad = Parsica\satisfy(Parsica\notPred($bad));
    $not_ws = Parsica\satisfy(Parsica\notPred($ws));
    return $not_bad->and(Parsica\zeroOrMore($not_ws));
}

function identifier(): Parsica\Parser {
    $parser = Parsica\atLeastOne(Parsica\alphaNumChar()->or(Parsica\oneOfS("_-")));
    return $parser->label("identifier");
}

function arg(): Parsica\Parser {
    return Parsica\any(
        quoted_string(),
        unquoted_string()
    );
}

function filt(callable $cond, Parsica\Parser $parser): Parsica\Parser {
    $branch = fn ($x) => $cond($x)? Parsica\pure($x) 
                                  : Parsica\fail("filtered"); 
    return Parsica\bind($parser, $branch);
}

function positive_integer(): Parsica\Parser {
    $num = Parsica\atLeastOne(Parsica\digitChar());
    return $num->map(intval(...))->label("positive_integer");
}

function integer_below_60(): Parsica\Parser {
    $in60 = fn ($x) => ($x < 60);
    return filt($in60, positive_integer());
}

function time(): Parsica\Parser {
    $sep = Parsica\char(':');
    $enclose = fn ($x) => [$x];
    $seconds = integer_below_60()->label("seconds");
    $minutes = integer_below_60()->label("minutes")
                       ->thenIgnore($sep);
    $hours = positive_integer()->label("hours")
                               ->thenIgnore($sep);
    return Parsica\any(
        Parsica\collect($hours,          $minutes,        $seconds),
        Parsica\collect(Parsica\pure(0), $minutes,        $seconds),
        Parsica\collect(Parsica\pure(0), Parsica\pure(0), $seconds)
    )->label("[[hours:]minutes:]seconds");
}

function video_list(): Parsica\Parser {
    $start = Parsica\string(":");
    $list = Parsica\sepBy(Parsica\skipSpace1(), arg());
    return $start->then(Parsica\skipSpace())->then($list);
}

function priority(): Parsica\Parser {
    $in10 = fn ($x) => (0 < $x && $x <= 10);
    return filt($in10, positive_integer());
}

function planned(): Parsica\Parser {
    return Parsica\any(
        collect_with_spaces(arg(), priority()),
        Parsica\collect(arg(), Parsica\pure(5))
    )->map(fn ($pl) => ["name" => $pl[0], "priority" => $pl[1]]);
}
