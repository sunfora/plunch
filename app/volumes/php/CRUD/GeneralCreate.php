<?
namespace Plunch\CRUD;

require_once "CRUD/ErrorCode.php";
require_once "InternalException.php";

use Plunch\InternalException;

trait GeneralCreate {
    private function general_create(...$entities) {
        $rows = \array_map($this->row_from_entity(...), $entities);
        try {
            return $this->db->insert(self::TABLE, $rows);
        } catch (\mysqli_sql_exception $e) {
            if (defined('self::FAILED_CREATE_MESSAGES')) {
                $messages = self::FAILED_CREATE_MESSAGES; 
            } else {
                $messages = [];
            }
            $explain = fn ($default) => new InternalException(
                $messages[$e->getCode()] ?? $default
            );

            throw match(ErrorCode::tryFrom($e->getCode())) {
                ErrorCode::DUPLICATE_KEY => $explain("already exists"),
                ErrorCode::PARENT_CONSTRAINT_ON_ADD_OR_UPDATE => $explain("parent constraint failed"),
                default => $e
            };
        }
    } 
}
