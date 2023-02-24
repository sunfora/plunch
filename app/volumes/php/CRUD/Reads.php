<?
namespace Plunch\CRUD;

interface Reads {
    public function read_if_exists($value);
    public function read($value);
    public function read_where($conditions, $tail): Array;
}
