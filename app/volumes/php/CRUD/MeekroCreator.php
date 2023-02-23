<?
namespace Plunch\CRUD;

require_once "CRUD/MeekroOperation.php";
require_once "CRUD/Creates.php";

class MeekroCreator extends MeekroOperation implements Creates {
    public function __construct(private DataBaseTable $table, private \MeekroDB $db) {}

    protected function make_rows(...$values) {
        $make_row = $this->table->row_from_entity(...);
        $rows = \array_map($make_row, $values);
        return $rows;
    }

    public function create(...$values) {
        return $this->rethrow_explained(
            $this->create_no_explain(...),
            $this->make_error(...),
            ...$values
        );
    }

    public function create_no_explain(...$values) {
        return $this->db->insert(
            $this->table->name(), $this->make_rows(...$values)
        );
    }
}
