<?
namespace Plunch\CRUD;

trait GeneralUpdate {
    private function general_update($first, $second) {
        return $this->db->update(
            self::TABLE, 
            $this->row_from_entity($second),
            '%l', $this->locate($first)
        );
    }
}
