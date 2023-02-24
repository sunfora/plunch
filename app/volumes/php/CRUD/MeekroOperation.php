<?
namespace Plunch\CRUD;

require_once "CRUD/Explains.php";
require_once "CRUD/RethrowExplained.php";
require_once "CRUD/ErrorCode.php";
require_once "CRUD/DataBaseTable.php";
require_once "InternalException.php";

use Plunch\InternalException;

abstract class MeekroOperation implements Explains {
    use RethrowExplained;

    public function __construct(
        protected DataBaseTable $table, 
        protected \MeekroDB $db
    ) {}

    public function explain(\Throwable $e, ...$args): ?string {
        return $this->match_error_case($e)?->explain_in_default_way();
    }

    protected function table_expr(): string {
        return '`' . $this->table->name() . '`';
    }

    protected function match_error_case(\Throwable $e): ?ErrorCode {
        if (! $e instanceof \mysqli_sql_exception) {
            return null;
        }
        return ErrorCode::tryFrom($e->getCode());
    }
    
    protected function make_error(string $str): \Throwable {
        return new InternalException($str);
    }
}
