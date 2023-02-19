<?
namespace Plunch\CRUD;

trait GeneralCreate {
    private function general_create(...$entities) {
        $rows = \array_map($this->row_from_entity(...), $entities);
        return $this->db->insert(self::TABLE, $rows);
    } 
}
