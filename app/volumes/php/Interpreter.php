<?
require "/vendor/autoload.php";
require_once "Parsers.php";

use Parsica\Parsica as Parsica;

final class Interpreter 
{
    public function __construct(private Array $syntax, private $core) {
        assert((bool)$syntax, "Error: empty syntax");
    }

    private static function arg_parser_from($spec) {
        return Parsers\collect_with_spaces(...array_map(fn ($c) => "Parsers\\$c"(), $spec));
    }

    private static function cmd_parser_from($spec) {
        return Parsers\collect_with_spaces(...array_map(Parsica\string(...), $spec));
    }
    
    private static function parser_from($cmd_spec, $arg_spec) {
        $whitespaces = Parsica\skipSpace();
        
        $collect = ($arg_spec)? Parsers\collect_with_spaces(...) : Parsica\collect(...);
        
        $cmd_with_args = $collect(
            static::cmd_parser_from($cmd_spec),
            static::arg_parser_from($arg_spec)
        );

        return Parsica\between(
            $whitespaces, $whitespaces,
            $cmd_with_args
        )->thenEof();
    }

    public function execute(string $command) {
        $stream = new Parsica\StringStream($command);
        
        $failure = null;
        $known_command = false;

        foreach ($this->syntax as $method => [$cmd_spec, $args_spec]) {
            $full_parser = static::parser_from($cmd_spec, $args_spec);
            $result = $full_parser->run($stream);
            
            if ($result->isSuccess()) {
                [1 => $args] = $result->output();
                return $this->core->$method(...$args);
            }

            // just trying to get better error messages from this point
            
            [$new_known, $new_failure] = $this->update_failure(
                $known_command, $stream, 
                $cmd_spec, $args_spec
            );

            $known_command = $known_command || $new_known;  
            $failure = ($new_failure !== null)? $new_failure : $failure; 
        }

        $failure->throw();
    }

    private function update_failure($known_command, $stream, $cmd_spec, $args_spec) {
        $cmd_parser = static::cmd_parser_from($cmd_spec);
        $cmd_parser = Parsica\skipSpace()->then($cmd_parser);

        $result = $cmd_parser->run($stream);

        if ($result->isFail()) {
            if (! $known_command) {
                return [false, $result];
            }
            return [false, null];
        } 

        $spaces_after_cmd = ($args_spec)? Parsica\skipSpace1() : Parsica\skipSpace();
        $arg_parser = Parsica\between(
            $spaces_after_cmd, Parsica\skipSpace(),
            static::arg_parser_from($args_spec)
        )->thenEof();

        return [true, $result->continueWith($arg_parser)];
    }
}

