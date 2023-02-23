<?
namespace Plunch\CRUD;

require_once "CRUD/MeekroOperation.php";
require_once "CRUD/Deletes.php";

class MeekroDeleter extends MeekroOperation implements Deletes {
    public function __construct(private DataBaseTable $table, private \MeekroDB $db) {}

    public function delete($value) {
        return $this->rethrow_explained(
            $this->delete_no_explain(...),
            $this->make_error(...),
            $value
        );
    }

    public function delete_no_explain($value) {
        return $this->db->delete(
            $this->table->name(), '%l', 
            $this->table->locate($value)
        );
    }
}