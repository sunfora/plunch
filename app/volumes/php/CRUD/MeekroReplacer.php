<?
namespace Plunch\CRUD;

require_once "CRUD/MeekroOperation.php";
require_once "CRUD/Replaces.php";

class MeekroReplacer extends MeekroOperation implements Replaces {

    public function replace($value) {
        return $this->rethrow_explained(
            $this->replace_no_explain(...),
            $this->make_error(...),
            $value
        );
    }

    public function replace_no_explain($value) {
        return $this->db->replace(
            $this->table_expr(), 
            $this->table->row_from_entity($value)
        );
    }
}
