<?
namespace Plunch\CRUD;

trait GeneralReplace {
    private function general_replace($entity) {
        return $this->db->replace(self::TABLE, $this->row_from_entity($entity));
    } 
}
