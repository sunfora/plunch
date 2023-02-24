<?
namespace Plunch\CRUD;

require_once "CRUD/MeekroOperation.php";
require_once "CRUD/Deletes.php";

class MeekroDeleter extends MeekroOperation implements Deletes {
    
    public function delete($value) {
        return $this->rethrow_explained(
            $this->delete_no_explain(...),
            $this->make_error(...),
            $value
        );
    }

    public function delete_where($location) {
        return $this->rethrow_explained(
            $this->delete_where_no_explain(...),
            $this->make_error(...),
            $location
        );
    }

    public function delete_where_no_explain($location) {
        return $this->db->delete(
            $this->table_expr(), '%l', $location
        );
    }

    public function delete_no_explain($value) {
        return $this->delete_where_no_explain($this->table->locate($value));
    }
}
