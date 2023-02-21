<?
namespace Plunch\CRUD;
require_once "InternalException.php";

use Plunch\{InternalException};


trait GeneralRead {
    private function read_from() {
        return '`' . self::TABLE . '`';
    }

    private static function make_schema_from(Array $schema): string {
        return \implode(', ', $schema);
    }

    private function read_schema() {
        return $this->make_schema_from(self::SCHEMA);
    }

    private function general_read_if_exists($entity) {
        $row = $this->db->queryFirstRow(
            "SELECT %l FROM %l WHERE %l",
            $this->read_schema(),
            $this->read_from(),
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
        $query = "SELECT %l FROM %l WHERE %l %l";
        $rows = $this->db->query(
            $query, 
            $this->read_schema(),
            $this->read_from(),
            $locate_rows,
            $tail
        );
        return \array_map($this->entity_from_row(...), $rows);
    }
}
