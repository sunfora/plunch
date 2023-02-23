<?
namespace Plunch\CRUD;

require_once "CRUD/MeekroOperation.php";
require_once "CRUD/Updates.php";

class MeekroUpdater extends MeekroOperation implements Updates {
    public function __construct(private DataBaseTable $table, private \MeekroDB $db) {}

    public function update($first, $second) {
        return $this->rethrow_explained(
            $this->update_no_explain(...),
            $this->make_error(...),
            $first, $second
        );
    }

    public function update_no_explain($first, $second) {
        return $this->db->update(
            $this->table->name(), 
            $this->table->row_from_entity($second),
            '%l', $this->table->locate($first)
        );
    }
}
