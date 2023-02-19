<?
namespace Plunch\CRUD;
require_once "InternalException.php";

use Plunch\{InternalException};

function make_params(Array $schema) {
    return \implode(', ', $schema);
}

trait GeneralRead {
    private function general_read_if_exists($entity) {
        $row = $this->db->queryFirstRow(
            "SELECT %l FROM `%l` WHERE %l",
            make_params(self::SCHEMA),
            self::TABLE,
            $this->locate($entity)
        );
        return ($row !== null)? $this->entity_from_row($row) : null;
    }

    private function general_read(string $message, $entity) {
        $value = $this->general_read_if_exists($entity);
        if ($value === null) {
            throw new InternalException($message);
        }
        return $value;
    }

    private function general_does_exist($entity) {
        return $this->general_read_if_exists($entity) !== null;
    }

    private function general_read_where($locate_rows, string $tail="") {
        $query = "SELECT %l FROM `%l` WHERE %l %l";
        $rows = $this->db->query(
            $query, 
            make_params(self::SCHEMA),
            self::TABLE,
            $locate_rows,
            $tail
        );
        return \array_map($this->entity_from_row(...), $rows);
    }
}
