<?
namespace Plunch\CRUD;

require_once "CRUD/MeekroOperation.php";
require_once "CRUD/Reads.php";
require_once "InternalException.php";

use Plunch\InternalException;

class MeekroReader extends MeekroOperation implements Reads {

    protected static function make_schema_from(Array $schema): string {
        return \implode(', ', $schema);
    }

    protected function read_schema() {
        return $this->make_schema_from($this->table->schema());
    }

    protected function not_exists_message($entity): string {
        return "value does not exist"; 
    }

    public function read($entity) {
        $value = $this->read_if_exists($entity);
        if ($value === null) {
            throw new InternalException($this->not_exists_message($entity));
        }
        return $value;
    }

    public function read_if_exists($value) {
        $values = $this->read_where($this->table->locate($value));
        if ($values === []) {
            return null;
        }
        return $values[0];
    }

    public function read_where($locate_rows, $tail=""): Array {
        $rows = $this->db->query(
            "SELECT %l FROM %l WHERE %l %l", 
            $this->read_schema(),
            $this->table_expr(),
            $locate_rows,
            $tail
        );
        return \array_map($this->table->entity_from_row(...), $rows);
    }
}
