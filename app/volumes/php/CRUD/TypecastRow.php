<?
namespace Plunch\CRUD;
require_once "Table.php";
use function Plunch\Util\Table\schemap_row;

trait TypecastRow {
    abstract private function typecast_kv($key, $value);

    private function typecast_row(Array $row) {
        return schemap_row($this->typecast_kv(...), $row, self::SCHEMA);
    }
}
