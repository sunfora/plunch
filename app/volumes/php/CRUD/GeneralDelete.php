<?
namespace Plunch\CRUD;

trait GeneralDelete {
    private function general_delete($entity) {
        return $this->db->delete(self::TABLE, '%l', $this->locate($entity));
    } 
}
