<?
namespace Plunch\CRUD;

interface DataBaseTable {
    public function entity_from_row(Array $row);
    public function row_from_entity($entity): Array;
    public function locate($entity);
    public function name(): string;
    public function schema(): Array;
}
